# WordPress Plugin with MCP Server

This project implements a WordPress plugin with an integrated MCP (Model Context Protocol) server for SEO content generation and analysis.

## Project Structure

```
wordpress-plugin-with-mcp-server/
├── backend/
│   ├── api/
│   │   └── mcp-server.py          # MCP Protocol Server Implementation
│   ├── main.py                    # Main FastAPI application
│   └── requirements.txt           # Python dependencies
├── frontend/                      # React frontend application
├── temp-mcp-vercel/
│   └── api/
│       └── server.ts              # Vercel MCP adapter example
├── vercel.json                    # Vercel deployment configuration
└── README.md                      # This file
```

## MCP Server Features

The MCP server provides the following capabilities with **bilingual support (English/Thai)**:

### Tools
1. **content_generation** - Generate SEO-optimized content for various industries
   - English: "Generate SEO-optimized content"
   - Thai: "สร้างเนื้อหาที่เหมาะสมกับ SEO"
2. **seo_analysis** - Analyze SEO performance of content or URLs
   - English: "Analyze SEO performance"
   - Thai: "วิเคราะห์ประสิทธิภาพ SEO"
3. **keyword_research** - Research keywords for SEO optimization
   - English: "Research keywords for SEO"
   - Thai: "วิจัยคำค้นหาสำหรับ SEO"

### Prompts
1. **blog_post** - Generate blog post prompts for specific topics and industries
   - English: "Generate blog post prompts"
   - Thai: "สร้างคำแนะนำสำหรับบทความบล็อก"

### Resources
1. **industry_data** - Access comprehensive data about specific industries
   - English: "Access industry data"
   - Thai: "เข้าถึงข้อมูลอุตสาหกรรม"

## MCP Protocol Implementation

The server implements the MCP protocol with the following endpoints:

- `POST /mcp-server` - Main MCP protocol endpoint
- `GET /mcp-server/health` - Health check endpoint

### Supported MCP Methods

- `initialize` - Initialize the MCP connection
- `tools/list` - List available tools
- `tools/call` - Execute a tool
- `prompts/list` - List available prompts
- `prompts/get` - Get a prompt
- `resources/list` - List available resources
- `resources/read` - Read a resource

## Usage Examples

### Tool Usage

#### Content Generation (English)
```json
{
  "jsonrpc": "2.0",
  "id": 1,
  "method": "tools/call",
  "params": {
    "name": "content_generation",
    "arguments": {
      "topic": "Digital Marketing Strategies",
      "content_type": "blog_post",
      "keywords": ["SEO", "content marketing", "digital strategy"],
      "industry": "technology",
      "language": "en"
    }
  }
}
```

#### Content Generation (Thai)
```json
{
  "jsonrpc": "2.0",
  "id": 1,
  "method": "tools/call",
  "params": {
    "name": "content_generation",
    "arguments": {
      "topic": "กลยุทธ์การตลาดดิจิทัล",
      "content_type": "blog_post",
      "keywords": ["SEO", "การตลาดเนื้อหา", "กลยุทธ์ดิจิทัล"],
      "industry": "เทคโนโลยี",
      "language": "th"
    }
  }
}
```

#### SEO Analysis (English)
```json
{
  "jsonrpc": "2.0",
  "id": 2,
  "method": "tools/call",
  "params": {
    "name": "seo_analysis",
    "arguments": {
      "url": "https://example.com/blog-post",
      "content": "Your content to analyze...",
      "language": "en"
    }
  }
}
```

#### SEO Analysis (Thai)
```json
{
  "jsonrpc": "2.0",
  "id": 2,
  "method": "tools/call",
  "params": {
    "name": "seo_analysis",
    "arguments": {
      "url": "https://example.com/blog-post",
      "content": "เนื้อหาของคุณที่ต้องการวิเคราะห์...",
      "language": "th"
    }
  }
}
```

#### Keyword Research (English)
```json
{
  "jsonrpc": "2.0",
  "id": 3,
  "method": "tools/call",
  "params": {
    "name": "keyword_research",
    "arguments": {
      "seed_keyword": "digital marketing",
      "industry": "technology",
      "language": "en"
    }
  }
}
```

#### Keyword Research (Thai)
```json
{
  "jsonrpc": "2.0",
  "id": 3,
  "method": "tools/call",
  "params": {
    "name": "keyword_research",
    "arguments": {
      "seed_keyword": "การตลาดดิจิทัล",
      "industry": "เทคโนโลยี",
      "language": "th"
    }
  }
}
```

### Prompt Usage

#### Blog Post Prompt (English)
```json
{
  "jsonrpc": "2.0",
  "id": 4,
  "method": "prompts/get",
  "params": {
    "name": "blog_post",
    "arguments": {
      "topic": "AI in Marketing",
      "industry": "technology",
      "language": "en"
    }
  }
}
```

#### Blog Post Prompt (Thai)
```json
{
  "jsonrpc": "2.0",
  "id": 4,
  "method": "prompts/get",
  "params": {
    "name": "blog_post",
    "arguments": {
      "topic": "AI ในการตลาด",
      "industry": "เทคโนโลยี",
      "language": "th"
    }
  }
}
```

### Resource Usage

#### Industry Data (English)
```json
{
  "jsonrpc": "2.0",
  "id": 5,
  "method": "resources/read",
  "params": {
    "uri": "industry://data/technology",
    "language": "en"
  }
}
```

#### Industry Data (Thai)
```json
{
  "jsonrpc": "2.0",
  "id": 5,
  "method": "resources/read",
  "params": {
    "uri": "industry://data/technology",
    "language": "th"
  }
}
```

## Deployment

### Vercel Deployment

The project is configured for deployment on Vercel with the following routes:

- `/mcp-server` - Routes to the MCP server
- `/api/*` - Routes to the main FastAPI application
- `/*` - Routes to the React frontend

### Local Development

1. Install Python dependencies:
```bash
cd backend
pip install -r requirements.txt
```

2. Install Node.js dependencies:
```bash
cd frontend
npm install
```

3. Run the backend:
```bash
cd backend
uvicorn main:app --reload
```

4. Run the frontend:
```bash
cd frontend
npm start
```

## MCP Client Integration

To integrate with MCP clients (like Claude Desktop), configure your client to connect to:

```
https://your-domain.vercel.app/mcp-server
```

### Claude Desktop Configuration

Add to your Claude Desktop configuration:

```json
{
  "mcpServers": {
    "seo-forge": {
      "command": "curl",
      "args": [
        "-X", "POST",
        "-H", "Content-Type: application/json",
        "-d", "@-",
        "https://your-domain.vercel.app/mcp-server"
      ]
    }
  }
}
```

## API Documentation

### Health Check
```
GET /mcp-server/health
```

Returns server status and information.

### MCP Protocol Endpoint
```
POST /mcp-server
```

Accepts MCP protocol JSON-RPC requests and returns appropriate responses.

## Error Handling

The server implements proper error handling for:
- Invalid JSON-RPC requests
- Unknown methods
- Missing required parameters
- Tool execution errors

All errors are returned in JSON-RPC error format:

```json
{
  "jsonrpc": "2.0",
  "id": 1,
  "error": {
    "code": -32603,
    "message": "Error description"
  }
}
```

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests if applicable
5. Submit a pull request

## License

This project is licensed under the MIT License.
