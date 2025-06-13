/**
 * Health Check Endpoint for Vercel
 */

import { VercelRequest, VercelResponse } from '@vercel/node';

export default async function handler(req: VercelRequest, res: VercelResponse) {
  // CORS headers
  res.setHeader('Access-Control-Allow-Origin', '*');
  res.setHeader('Access-Control-Allow-Methods', 'GET, OPTIONS');
  res.setHeader('Access-Control-Allow-Headers', 'Content-Type');

  if (req.method === 'OPTIONS') {
    return res.status(200).end();
  }

  if (req.method !== 'GET') {
    return res.status(405).json({ error: 'Method not allowed' });
  }

  try {
    const healthData = {
      status: 'healthy',
      timestamp: new Date().toISOString(),
      version: '2.0.0',
      environment: process.env.NODE_ENV || 'development',
      services: {
        mcp_server: 'operational',
        content_generation: checkService('GOOGLE_API_KEY', 'OPENAI_API_KEY', 'ANTHROPIC_API_KEY'),
        image_generation: checkService('REPLICATE_API_TOKEN', 'OPENAI_API_KEY'),
        seo_analysis: 'operational',
        thai_translation: checkService('GOOGLE_API_KEY', 'OPENAI_API_KEY'),
        keyword_research: 'operational',
        wordpress_integration: 'operational',
        authentication: 'operational',
        cache: 'operational'
      },
      api_endpoints: {
        health: '/health',
        mcp_server: '/mcp',
        client: '/client',
        tools_list: '/mcp/tools/list',
        tools_execute: '/mcp/tools/execute',
        auth_login: '/mcp/auth/login',
        auth_register: '/mcp/auth/register'
      },
      deployment: {
        platform: 'vercel',
        region: process.env.VERCEL_REGION || 'unknown',
        deployment_id: process.env.VERCEL_DEPLOYMENT_ID || 'local'
      }
    };

    return res.status(200).json(healthData);
  } catch (error) {
    return res.status(500).json({
      status: 'unhealthy',
      timestamp: new Date().toISOString(),
      error: error instanceof Error ? error.message : 'Unknown error'
    });
  }
}

function checkService(...envVars: string[]): string {
  const hasAnyKey = envVars.some(envVar => process.env[envVar]);
  return hasAnyKey ? 'operational' : 'limited';
}
