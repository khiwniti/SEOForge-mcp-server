# WordPress Plugin Update Guide for Express MCP Server

This guide explains how to update the SEO Forge WordPress plugin to work with the new Express.js MCP server backend.

## Overview

The WordPress plugin needs to be updated to connect properly with our new Express.js backend that uses the unified MCP (Model Context Protocol) server architecture. The main changes involve:

1. **API endpoint updates** - New endpoint URLs
2. **Request/response format changes** - MCP response structure
3. **Authentication method updates** - API key authentication
4. **Default server URL** - Point to the new Express server

## Files to Update

### 1. Main Plugin File (`seo-forge.php`)

**Line 229**: Update the default API URL in the localization script:

```php
// OLD
'apiUrl' => get_option( 'seo_forge_api_url', 'https://seoforge-mcp-platform.vercel.app' ),

// NEW
'apiUrl' => get_option( 'seo_forge_api_url', 'http://localhost:8000' ),
```

### 2. API Handler (`includes/class-api.php`)

**Replace the entire file** with the updated version (`class-api-updated.php`) that includes:

- Updated API endpoints to match Express server
- MCP response format handling
- Proper authentication with API keys
- Better error handling and logging

## Key Changes Made

### API Endpoint Mapping

| Old Endpoint | New Endpoint | Purpose |
|-------------|-------------|---------|
| `/api/status` | `/health` | Health check |
| `/api/generate-content` | `/api/blog-generator/generate` | Content generation |
| `/api/analyze-seo` | `/api/seo-analyzer/analyze` | SEO analysis |
| `/api/research-keywords` | `/api/keyword-research/analyze` | Keyword research |
| `/api/generate-flux-image` | `/api/flux-image-gen/generate` | Image generation |
| `/api/generate-flux-batch` | Multiple calls to single endpoint | Batch image generation |

### Authentication Changes

**Old Method:**
```php
if ( ! empty( $this->api_key ) ) {
    $args['headers']['Authorization'] = 'Bearer ' . $this->api_key;
}
```

**New Method:**
```php
if ( ! empty( $this->api_key ) ) {
    $args['headers']['X-API-Key'] = $this->api_key;
}
```

### Response Format Handling

**Old Format:**
```json
{
  "content": "...",
  "title": "...",
  "meta_description": "..."
}
```

**New MCP Format:**
```json
{
  "success": true,
  "result": {
    "content": "...",
    "title": "...",
    "meta_description": "..."
  },
  "tool": "generate_content",
  "executionTime": 1234,
  "timestamp": "2024-01-01T00:00:00.000Z"
}
```

## Installation Steps

### Option 1: Manual File Replacement

1. **Backup your current plugin files**
2. **Replace `includes/class-api.php`** with the updated version
3. **Update line 229 in `seo-forge.php`** with the new default API URL
4. **Clear any caches** (if using caching plugins)

### Option 2: Plugin Settings Update

If you prefer not to modify files:

1. Go to **WordPress Admin → SEO Forge → Settings**
2. Update the **API URL** to: `http://localhost:8000`
3. Update the **API Key** to: `dev-api-key-1` (or your custom key)
4. Click **Save Settings**
5. Test the connection

## Configuration

### Server Settings

Update these settings in your WordPress admin:

- **API URL**: `http://localhost:8000` (or your server URL)
- **API Key**: `dev-api-key-1` (or your custom API key)

### Environment Variables

Make sure your Express server has these environment variables:

```bash
# Server Configuration
NODE_ENV=development
PORT=8000
HOST=0.0.0.0

# API Keys for Authentication
VALID_API_KEYS=dev-api-key-1,dev-api-key-2

# AI Service API Keys
OPENAI_API_KEY=your-openai-api-key
GOOGLE_API_KEY=your-google-api-key
# ... other API keys
```

## Testing the Connection

### 1. Start the Express Server

```bash
cd backend-express
npm run dev
```

### 2. Test from WordPress Admin

1. Go to **SEO Forge → Settings**
2. Click **Test Connection**
3. You should see: "Connection successful!"

### 3. Test Each Feature

- **Content Generation**: Try generating a blog post
- **SEO Analysis**: Analyze some content
- **Keyword Research**: Research some keywords
- **Image Generation**: Generate an image with Flux

## Troubleshooting

### Common Issues

#### 1. Connection Failed
**Error**: "Connection failed: cURL error 7: Failed to connect"
**Solution**: 
- Make sure the Express server is running
- Check the API URL in settings
- Verify firewall/network settings

#### 2. Authentication Failed
**Error**: "Access denied. Please check your API key"
**Solution**:
- Verify the API key in WordPress settings
- Check `VALID_API_KEYS` in server environment
- Ensure the key matches exactly

#### 3. Invalid Response Format
**Error**: "Invalid response from API"
**Solution**:
- Make sure you're using the updated `class-api.php`
- Check server logs for errors
- Verify the server is returning proper JSON

#### 4. Endpoint Not Found
**Error**: "API endpoint not found"
**Solution**:
- Verify the Express server is running the latest version
- Check that all routes are properly loaded
- Test the endpoint directly with curl

### Debug Mode

Enable debug logging by adding this to your `wp-config.php`:

```php
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
```

Then check `/wp-content/debug.log` for detailed API request/response logs.

## Advanced Configuration

### Custom API Endpoints

If you need to customize endpoints, modify the `make_request()` calls in `class-api.php`:

```php
// Example: Custom content generation endpoint
$response = $this->make_request( '/api/custom-content-generator', $data );
```

### Custom Authentication

For custom authentication methods, modify the `make_request()` method:

```php
// Example: JWT token authentication
if ( ! empty( $this->api_key ) ) {
    $args['headers']['Authorization'] = 'Bearer ' . $this->api_key;
}
```

### Batch Processing

The updated plugin handles batch image generation by making multiple single requests. You can customize this behavior in the `generate_flux_batch()` method.

## Verification Checklist

- [ ] Express MCP server is running
- [ ] WordPress plugin files are updated
- [ ] API URL is configured correctly
- [ ] API key is set and valid
- [ ] Connection test passes
- [ ] Content generation works
- [ ] SEO analysis works
- [ ] Keyword research works
- [ ] Image generation works
- [ ] Error handling works properly
- [ ] Debug logging is available

## Support

If you encounter issues:

1. **Check the server logs**: `backend-express/logs/`
2. **Check WordPress debug logs**: `/wp-content/debug.log`
3. **Test endpoints directly**: Use curl or Postman
4. **Verify environment variables**: Check server configuration
5. **Review the migration guide**: `MIGRATION_GUIDE.md`

## Benefits After Update

- **Better Performance**: Faster response times with Express.js
- **Unified Architecture**: All services through MCP protocol
- **Enhanced Error Handling**: Better error messages and logging
- **Improved Reliability**: More stable connections and responses
- **Future-Proof**: Ready for new MCP features and tools