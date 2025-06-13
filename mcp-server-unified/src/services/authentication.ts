/**
 * Authentication Service
 * Handles authentication, authorization, and security for the MCP server
 */

import jwt from 'jsonwebtoken';
import bcrypt from 'bcryptjs';
import { v4 as uuidv4 } from 'uuid';

interface User {
  id: string;
  email: string;
  password_hash: string;
  api_key: string;
  role: 'admin' | 'user' | 'readonly';
  created_at: Date;
  last_login?: Date;
  is_active: boolean;
}

interface AuthRequest {
  email?: string;
  password?: string;
  api_key?: string;
  token?: string;
}

interface AuthResponse {
  success: boolean;
  user?: Partial<User>;
  token?: string;
  api_key?: string;
  error?: string;
}

export class AuthenticationService {
  private initialized = false;
  private jwtSecret: string;
  private users: Map<string, User> = new Map();

  constructor() {
    this.jwtSecret = process.env.JWT_SECRET || 'default-secret-change-in-production';
  }

  async initialize(): Promise<void> {
    if (this.initialized) return;
    
    // Create default admin user if none exists
    await this.createDefaultUser();
    
    this.initialized = true;
  }

  async authenticate(request: AuthRequest): Promise<AuthResponse> {
    if (!this.initialized) {
      throw new Error('Service not initialized');
    }

    try {
      // API Key authentication
      if (request.api_key) {
        return await this.authenticateWithApiKey(request.api_key);
      }

      // JWT Token authentication
      if (request.token) {
        return await this.authenticateWithToken(request.token);
      }

      // Email/Password authentication
      if (request.email && request.password) {
        return await this.authenticateWithCredentials(request.email, request.password);
      }

      return {
        success: false,
        error: 'No valid authentication method provided'
      };
    } catch (error) {
      return {
        success: false,
        error: error instanceof Error ? error.message : 'Authentication failed'
      };
    }
  }

  async createUser(email: string, password: string, role: 'admin' | 'user' | 'readonly' = 'user'): Promise<AuthResponse> {
    try {
      // Check if user already exists
      const existingUser = Array.from(this.users.values()).find(u => u.email === email);
      if (existingUser) {
        return {
          success: false,
          error: 'User already exists'
        };
      }

      // Hash password
      const password_hash = await bcrypt.hash(password, 12);
      
      // Generate API key
      const api_key = this.generateApiKey();

      // Create user
      const user: User = {
        id: uuidv4(),
        email,
        password_hash,
        api_key,
        role,
        created_at: new Date(),
        is_active: true
      };

      this.users.set(user.id, user);

      return {
        success: true,
        user: {
          id: user.id,
          email: user.email,
          role: user.role,
          api_key: user.api_key
        },
        api_key: user.api_key
      };
    } catch (error) {
      return {
        success: false,
        error: error instanceof Error ? error.message : 'User creation failed'
      };
    }
  }

  async generateToken(userId: string): Promise<string> {
    const user = this.users.get(userId);
    if (!user) {
      throw new Error('User not found');
    }

    const payload = {
      userId: user.id,
      email: user.email,
      role: user.role,
      iat: Math.floor(Date.now() / 1000),
      exp: Math.floor(Date.now() / 1000) + (24 * 60 * 60) // 24 hours
    };

    return jwt.sign(payload, this.jwtSecret);
  }

  async validateToken(token: string): Promise<{ valid: boolean; payload?: any; error?: string }> {
    try {
      const payload = jwt.verify(token, this.jwtSecret);
      return { valid: true, payload };
    } catch (error) {
      return { 
        valid: false, 
        error: error instanceof Error ? error.message : 'Invalid token' 
      };
    }
  }

  async refreshApiKey(userId: string): Promise<AuthResponse> {
    const user = this.users.get(userId);
    if (!user) {
      return {
        success: false,
        error: 'User not found'
      };
    }

    user.api_key = this.generateApiKey();
    this.users.set(userId, user);

    return {
      success: true,
      api_key: user.api_key
    };
  }

  async deactivateUser(userId: string): Promise<AuthResponse> {
    const user = this.users.get(userId);
    if (!user) {
      return {
        success: false,
        error: 'User not found'
      };
    }

    user.is_active = false;
    this.users.set(userId, user);

    return {
      success: true,
      user: {
        id: user.id,
        email: user.email,
        is_active: user.is_active
      }
    };
  }

  // Rate limiting and security
  private rateLimitMap = new Map<string, { count: number; resetTime: number }>();

  checkRateLimit(identifier: string, maxRequests: number = 100, windowMs: number = 60000): boolean {
    const now = Date.now();
    const record = this.rateLimitMap.get(identifier);

    if (!record || now > record.resetTime) {
      this.rateLimitMap.set(identifier, {
        count: 1,
        resetTime: now + windowMs
      });
      return true;
    }

    if (record.count >= maxRequests) {
      return false;
    }

    record.count++;
    this.rateLimitMap.set(identifier, record);
    return true;
  }

  // WordPress-specific authentication
  async authenticateWordPress(siteUrl: string, nonce: string, timestamp: number): Promise<AuthResponse> {
    try {
      // Validate nonce lifetime (24 hours)
      const nonceLifetime = 24 * 60 * 60 * 1000;
      if (Date.now() - timestamp > nonceLifetime) {
        return {
          success: false,
          error: 'Nonce expired'
        };
      }

      // Verify nonce (simplified - in production, use proper WordPress nonce verification)
      const expectedNonce = this.generateWordPressNonce(siteUrl, timestamp);
      if (nonce !== expectedNonce) {
        return {
          success: false,
          error: 'Invalid nonce'
        };
      }

      // Create temporary token for WordPress requests
      const token = jwt.sign(
        { 
          site_url: siteUrl, 
          type: 'wordpress',
          exp: Math.floor(Date.now() / 1000) + 3600 // 1 hour
        },
        this.jwtSecret
      );

      return {
        success: true,
        token
      };
    } catch (error) {
      return {
        success: false,
        error: 'WordPress authentication failed'
      };
    }
  }

  private async authenticateWithApiKey(apiKey: string): Promise<AuthResponse> {
    const user = Array.from(this.users.values()).find(u => u.api_key === apiKey && u.is_active);
    
    if (!user) {
      return {
        success: false,
        error: 'Invalid API key'
      };
    }

    // Update last login
    user.last_login = new Date();
    this.users.set(user.id, user);

    return {
      success: true,
      user: {
        id: user.id,
        email: user.email,
        role: user.role
      }
    };
  }

  private async authenticateWithToken(token: string): Promise<AuthResponse> {
    const validation = await this.validateToken(token);
    
    if (!validation.valid) {
      return {
        success: false,
        error: validation.error
      };
    }

    const user = this.users.get(validation.payload.userId);
    if (!user || !user.is_active) {
      return {
        success: false,
        error: 'User not found or inactive'
      };
    }

    return {
      success: true,
      user: {
        id: user.id,
        email: user.email,
        role: user.role
      }
    };
  }

  private async authenticateWithCredentials(email: string, password: string): Promise<AuthResponse> {
    const user = Array.from(this.users.values()).find(u => u.email === email && u.is_active);
    
    if (!user) {
      return {
        success: false,
        error: 'Invalid credentials'
      };
    }

    const isValidPassword = await bcrypt.compare(password, user.password_hash);
    if (!isValidPassword) {
      return {
        success: false,
        error: 'Invalid credentials'
      };
    }

    // Update last login
    user.last_login = new Date();
    this.users.set(user.id, user);

    // Generate token
    const token = await this.generateToken(user.id);

    return {
      success: true,
      user: {
        id: user.id,
        email: user.email,
        role: user.role
      },
      token
    };
  }

  private generateApiKey(): string {
    return `seoforge_${uuidv4().replace(/-/g, '')}`;
  }

  private generateWordPressNonce(siteUrl: string, timestamp: number): string {
    const data = `${siteUrl}:${timestamp}:${this.jwtSecret}`;
    return Buffer.from(data).toString('base64').substring(0, 32);
  }

  private async createDefaultUser(): Promise<void> {
    const defaultEmail = process.env.DEFAULT_ADMIN_EMAIL || 'admin@seoforge.dev';
    const defaultPassword = process.env.DEFAULT_ADMIN_PASSWORD || 'admin123';

    // Check if admin user already exists
    const existingAdmin = Array.from(this.users.values()).find(u => u.role === 'admin');
    if (existingAdmin) {
      return;
    }

    await this.createUser(defaultEmail, defaultPassword, 'admin');
  }

  // Utility methods
  getUserById(userId: string): User | undefined {
    return this.users.get(userId);
  }

  getAllUsers(): Partial<User>[] {
    return Array.from(this.users.values()).map(user => ({
      id: user.id,
      email: user.email,
      role: user.role,
      created_at: user.created_at,
      last_login: user.last_login,
      is_active: user.is_active
    }));
  }

  async changePassword(userId: string, oldPassword: string, newPassword: string): Promise<AuthResponse> {
    const user = this.users.get(userId);
    if (!user) {
      return { success: false, error: 'User not found' };
    }

    const isValidOldPassword = await bcrypt.compare(oldPassword, user.password_hash);
    if (!isValidOldPassword) {
      return { success: false, error: 'Invalid current password' };
    }

    user.password_hash = await bcrypt.hash(newPassword, 12);
    this.users.set(userId, user);

    return { success: true };
  }
}
