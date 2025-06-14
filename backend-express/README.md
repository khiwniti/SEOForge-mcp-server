# SEOForge Express Backend

A modern Express.js backend that replaces the FastAPI implementation, using the unified MCP (Model Context Protocol) server architecture for all AI services.

## Features

- **Unified MCP Architecture**: All AI services (content generation, SEO analysis, image generation, etc.) are accessed through MCP tools
- **Express.js Framework**: Fast, minimalist web framework for Node.js
- **TypeScript**: Full TypeScript support for type safety and better development experience
- **Authentication**: JWT-based authentication with API key support
- **Rate Limiting**: Built-in rate limiting and security middleware
- **Logging**: Comprehensive logging with Winston
- **Validation**: Request validation with express-validator
- **Error Handling**: Centralized error handling with proper HTTP status codes
- **Health Checks**: Health, readiness, and liveness endpoints
- **Legacy API Compatibility**: Maintains compatibility with existing FastAPI endpoints

## Architecture

```
backend-express/
├── src/
│   ├── services/           # MCP service implementations
│   │   ├── mcp-service-manager.ts
│   │   ├── content-generation.ts
│   │   ├── seo-analysis.ts
│   │   ├── image-generation.ts
│   │   ├── wordpress.ts
│   │   ├── thai-language.ts
│   │   ├── keyword-research.ts
│   │   ├── authentication.ts
│   │   └── cache.ts
│   ├── middleware/         # Express middleware
│   │   ├── auth.ts
│   │   ├── error-handler.ts
│   │   └── request-logger.ts
│   ├── routes/            # API route handlers
│   │   ├── health.ts
│   │   ├── mcp.ts
│   │   ├── api.ts
│   │   └── auth.ts
│   └── server.ts          # Main server file
├── logs/                  # Log files
├── package.json
├── tsconfig.json
└── README.md
```

## Installation

1. **Install dependencies**:
   ```bash
   cd backend-express
   npm install
   ```

2. **Set up environment variables**:
   ```bash
   cp .env.example .env
   # Edit .env with your configuration
   ```

3. **Build the project**:
   ```bash
   npm run build
   ```

4. **Start the server**:
   ```bash
   # Development
   npm run dev
   
   # Production
   npm start
   ```

## API Endpoints

### Health Checks
- `GET /health` - Comprehensive health check
- `GET /health/ready` - Readiness probe
- `GET /health/live` - Liveness probe

### Authentication
- `POST /auth/login` - User login
- `POST /auth/register` - User registration
- `POST /auth/verify` - Token verification
- `POST /auth/refresh` - Token refresh

### MCP Protocol
- `GET /mcp/tools` - List available MCP tools
- `POST /mcp/tools/execute` - Execute MCP tool
- `POST /mcp/protocol` - Direct MCP protocol handler
- `GET /mcp/status` - MCP server status

### Legacy API Compatibility
- `POST /api/blog-generator/generate` - Generate blog content
- `POST /api/seo-analyzer/analyze` - Analyze SEO
- `POST /api/flux-image-gen/generate` - Generate images
- `POST /api/wordpress-manager/sync` - WordPress sync
- `POST /api/thai-language/translate` - Thai translation
- `POST /api/keyword-research/analyze` - Keyword research
- `POST /api/universal-mcp/execute` - Universal MCP tool execution

## MCP Tools

The backend provides the following MCP tools:

1. **generate_content** - Generate SEO-optimized content
2. **analyze_seo** - Perform SEO analysis
3. **generate_image** - Generate AI images
4. **wordpress_sync** - Sync with WordPress
5. **translate_thai** - Thai language translation
6. **research_keywords** - Keyword research and analysis

## Authentication

The backend supports multiple authentication methods:

1. **JWT Tokens**: For user authentication
2. **API Keys**: For service-to-service communication
3. **Optional Auth**: Some endpoints work without authentication

## Configuration

Key environment variables:

- `NODE_ENV` - Environment (development/production)
- `PORT` - Server port (default: 8000)
- `JWT_SECRET` - JWT signing secret
- `OPENAI_API_KEY` - OpenAI API key
- `GOOGLE_API_KEY` - Google API key
- `REDIS_URL` - Redis connection URL
- `DATABASE_URL` - Database connection URL

## Migration from FastAPI

This Express backend is designed to be a drop-in replacement for the FastAPI backend:

1. **Same API endpoints**: All existing endpoints are maintained
2. **Same request/response format**: Compatible with existing clients
3. **Enhanced features**: Better error handling, logging, and monitoring
4. **MCP integration**: All services now use the unified MCP architecture

## Development

```bash
# Install dependencies
npm install

# Start development server with hot reload
npm run dev

# Run tests
npm test

# Lint code
npm run lint

# Format code
npm run format

# Build for production
npm run build
```

## Deployment

The backend can be deployed to various platforms:

1. **Vercel**: Serverless deployment
2. **Docker**: Containerized deployment
3. **Traditional servers**: PM2 or systemd

## Monitoring

- **Health endpoints**: For load balancer health checks
- **Request logging**: All requests are logged with unique IDs
- **Error tracking**: Comprehensive error logging
- **Performance metrics**: Execution time tracking

## Security

- **Helmet**: Security headers
- **CORS**: Configurable CORS policy
- **Rate limiting**: Request rate limiting
- **Input validation**: Request validation
- **JWT**: Secure token-based authentication