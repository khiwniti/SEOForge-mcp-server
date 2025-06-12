# 🔧 SEO Forge Ultimate - Debug Console Guide

## 🚀 Comprehensive API Call Logging System

This version includes a complete debugging system that logs every API call, progress update, and error for easy troubleshooting.

## 📊 Debug Features

### ✅ Console Logging
- **Colored Console Output**: Different colors for different log levels
- **Timestamped Entries**: Every log entry includes precise timestamps
- **Performance Metrics**: Request duration tracking
- **Detailed Error Information**: Complete error context and stack traces

### ✅ Debug Panel (Visual Interface)
- **Floating Debug Button**: Click the 🔧 button in bottom-right corner
- **Real-time Statistics**: Live API call counts and success rates
- **Quick Actions**: Test API, view logs, clear logs
- **Auto-updating Stats**: Updates every 5 seconds

### ✅ Console Commands
Access these commands in browser console:

```javascript
// Get complete debug report
SEOForgeDebug.getReport()

// Clear all debug logs
SEOForgeDebug.clearLogs()

// Enable/disable logging
SEOForgeDebug.enableLogging()
SEOForgeDebug.disableLogging()

// Test API connection
SEOForgeDebug.testAPI()
```

## 🔍 How to Debug API Issues

### Step 1: Open Browser Console
1. Press **F12** or **Ctrl+Shift+I** (Windows/Linux)
2. Press **Cmd+Option+I** (Mac)
3. Click on **Console** tab

### Step 2: Enable Debug Panel
1. Look for the 🔧 button in bottom-right corner
2. Click it to open the debug panel
3. Monitor real-time statistics

### Step 3: Trigger API Calls
1. Use any SEO Forge feature (Content Generator, SEO Analyzer, etc.)
2. Watch console for detailed logging
3. Check debug panel for statistics

### Step 4: Analyze Logs
Look for these log types:

#### 🚀 API Call Started
```
[SEO Forge API] 2025-06-12T11:45:23.456Z
🚀 API Call Started: seo_forge_generate_content
Data: {endpoint: "seo_forge_generate_content", parameters: {...}}
```

#### ✅ API Call Successful
```
[SEO Forge SUCCESS] 2025-06-12T11:45:25.789Z
✅ API Call Successful: seo_forge_generate_content (2333.45ms)
Data: {endpoint: "...", response: {...}, duration: 2333.45}
```

#### ❌ API Call Failed
```
[SEO Forge ERROR] 2025-06-12T11:45:25.789Z
❌ API Call Failed: seo_forge_generate_content (1234.56ms)
Data: {endpoint: "...", error: {...}, duration: 1234.56}
```

#### 📊 Progress Updates
```
[SEO Forge PROGRESS] 2025-06-12T11:45:24.123Z
📊 Progress Update: Processing (45%)
Data: {step: "Processing", percentage: 45, details: {...}}
```

## 🛠️ Common Issues & Solutions

### Issue: No Console Logs Appearing
**Solution:**
```javascript
// Check if logging is enabled
console.log(SEOForge.Debug.enabled);

// Enable logging if disabled
SEOForgeDebug.enableLogging();
```

### Issue: 403 Forbidden Errors
**Look for:**
- Status code 403 in error logs
- "Access denied" messages
- Nonce verification failures

**Debug Steps:**
1. Check nonce value in request data
2. Verify user permissions
3. Check server error logs

### Issue: Network Errors (Status 0)
**Look for:**
- Status code 0 in error logs
- "Network error" messages
- CORS-related errors

**Debug Steps:**
1. Check internet connection
2. Verify server URL is accessible
3. Check for CORS issues

### Issue: Slow API Responses
**Look for:**
- High duration values (>5000ms)
- Timeout errors
- Progress stuck at certain percentages

**Debug Steps:**
1. Check network speed
2. Monitor server performance
3. Review API endpoint efficiency

## 📈 Performance Monitoring

### Request Duration Analysis
```javascript
// Get all API calls with durations
const report = SEOForgeDebug.getReport();
const durations = report.calls
    .filter(call => call.data && call.data.duration)
    .map(call => ({
        endpoint: call.data.endpoint,
        duration: call.data.duration
    }));

console.table(durations);
```

### Success Rate Calculation
```javascript
// Calculate success rate
const summary = SEOForgeDebug.getReport().summary;
const successRate = (summary.success / summary.total * 100).toFixed(2);
console.log(`Success Rate: ${successRate}%`);
```

## 🔧 Advanced Debugging

### Enable Verbose Logging
```javascript
// Enable maximum verbosity
SEOForge.Debug.enabled = true;
console.log('Verbose logging enabled');
```

### Export Debug Data
```javascript
// Export all debug data for analysis
const debugData = SEOForgeDebug.getReport();
console.log('Copy this data for support:');
console.log(JSON.stringify(debugData, null, 2));
```

### Monitor Specific Endpoints
```javascript
// Filter logs for specific endpoint
const contentLogs = SEOForge.Debug.apiCalls.filter(
    call => call.data && call.data.endpoint === 'seo_forge_generate_content'
);
console.table(contentLogs);
```

## 🚨 Error Reporting

When reporting issues, include:

1. **Browser Console Output**: Copy all SEO Forge log entries
2. **Debug Report**: Run `SEOForgeDebug.getReport()` and copy output
3. **Network Tab**: Check browser Network tab for failed requests
4. **WordPress Debug Log**: Check WordPress error logs
5. **Server Response**: Include any server error responses

## 📞 Support Information

### Debug Commands Summary
```javascript
// Essential debug commands
SEOForgeDebug.getReport()     // Complete debug report
SEOForgeDebug.clearLogs()     // Clear all logs
SEOForgeDebug.testAPI()       // Test API connection
SEOForge.Debug.enabled        // Check logging status
```

### Log Levels
- **INFO**: General information and status updates
- **SUCCESS**: Successful operations
- **WARNING**: Non-critical issues
- **ERROR**: Failed operations and errors
- **API**: API call tracking
- **PROGRESS**: Progress updates

---

**Version**: 1.3.0 Ultimate Edition
**Debug System**: Comprehensive API Call Logging
**Status**: Production Ready with Full Debugging ✅