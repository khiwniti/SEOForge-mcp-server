# SEO Forge API Troubleshooting Guide

## 🔧 Fixed Issues in Latest Version

### ✅ JavaScript Syntax Error (Line 558)
- **Problem**: Duplicate code causing syntax error
- **Solution**: Removed duplicate functions and fixed file structure
- **Status**: FIXED ✅

### ✅ 403 Forbidden Error
- **Problem**: AJAX security and CORS issues
- **Solutions Applied**:
  - Enhanced nonce verification with better error handling
  - Added proper CORS headers
  - Fixed localized script name mismatch (`seoForgeAjax` → `seoForge`)
  - Added non-privileged AJAX actions
  - Improved error handling for different HTTP status codes

### ✅ Progress Bar Implementation
- **Added**: Comprehensive progress tracking for all API calls
- **Features**:
  - Real-time progress indicators
  - Step-by-step process visualization
  - Estimated time remaining
  - Animated progress bars with shimmer effects
  - Cancel request functionality
  - Enhanced error messages

## 🚀 New Features Added

### Progress Bar System
```javascript
// Automatic progress tracking for all API calls
SEOForge.ajaxWithProgress({
    title: 'Generating AI Content',
    steps: ['Analyzing keywords', 'Generating content', 'Optimizing for SEO'],
    // ... other options
});
```

### Enhanced Error Handling
- Specific error messages for 403, 404, 500 status codes
- Network error detection
- API key validation
- Server configuration checks

### Improved Security
- Better nonce verification
- User capability checks
- Enhanced CORS support
- Debug logging for troubleshooting

## 🔍 Debugging Steps

### 1. Check WordPress Error Logs
```bash
# Enable WordPress debugging
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);

# Check logs at: /wp-content/debug.log
```

### 2. Verify API Configuration
- **Server URL**: Should end without trailing slash
- **API Key**: Check if required by your server
- **User Permissions**: Ensure user has `manage_options` capability

### 3. Test API Connection
1. Go to SEO Forge → Dashboard
2. Click "Test Connection"
3. Check browser console for errors
4. Review WordPress error logs

### 4. Browser Console Debugging
```javascript
// Check if SEO Forge object is loaded
console.log(seoForge);

// Test AJAX manually
jQuery.post(seoForge.ajaxUrl, {
    action: 'seo_forge_test_connection',
    nonce: seoForge.nonce
}, function(response) {
    console.log(response);
});
```

## 🛠️ Common Solutions

### 403 Forbidden Error
1. **Check nonce**: Refresh page to regenerate nonce
2. **User permissions**: Ensure user is admin
3. **Server configuration**: Check if server blocks requests
4. **API key**: Verify API key is correct

### Network Errors
1. **CORS**: Server must allow cross-origin requests
2. **SSL**: Check SSL certificate validity
3. **Firewall**: Ensure WordPress can reach API server
4. **Timeout**: Increase timeout in wp-config.php

### JavaScript Errors
1. **Clear cache**: Clear browser and WordPress cache
2. **Plugin conflicts**: Deactivate other plugins temporarily
3. **Theme conflicts**: Switch to default theme temporarily

## 📋 Server Requirements

### API Server
- Must respond to `/api/status` endpoint
- Should return JSON with `{"status": "healthy"}`
- Must handle CORS requests
- Should accept POST requests with JSON body

### WordPress
- WordPress 5.0+
- PHP 7.4+
- cURL extension enabled
- JSON extension enabled

## 🔧 Configuration Examples

### wp-config.php
```php
// Increase timeout for API requests
define('WP_HTTP_BLOCK_EXTERNAL', false);
define('WP_ACCESSIBLE_HOSTS', 'your-api-server.com');

// Enable debugging
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

### Server Headers (for API server)
```
Access-Control-Allow-Origin: *
Access-Control-Allow-Methods: POST, GET, OPTIONS
Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With
```

## 📞 Support

If issues persist after following this guide:

1. **Check WordPress error logs**
2. **Enable browser console logging**
3. **Test with default WordPress theme**
4. **Verify server is accessible**
5. **Contact support with error logs**

## 🎯 Quick Test Checklist

- [ ] JavaScript syntax error fixed
- [ ] Progress bars appear during API calls
- [ ] No 403 errors in browser console
- [ ] API connection test succeeds
- [ ] Error messages are descriptive
- [ ] All features work with progress tracking

---

**Version**: 1.2.0+
**Last Updated**: 2025-06-12
**Status**: All major issues resolved ✅