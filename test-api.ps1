# API Test Script using PowerShell

$API_URL = "https://seo-forge-mcp-server-7ufjigzbd-getintheqs-projects.vercel.app"
$API_KEY = "test-api-key"

Write-Host "Starting API Tests..."
Write-Host "====================================" -ForegroundColor Cyan

# Test Health Endpoint
Write-Host "Testing Health Endpoint..." -ForegroundColor Green
try {
    $headers = @{
        "X-API-Key" = $API_KEY
    }
    $response = Invoke-RestMethod -Uri "$API_URL/health" -Method Get -Headers $headers
    $response | ConvertTo-Json
} catch {
    Write-Host "Error: $_" -ForegroundColor Red
}
Write-Host "====================================" -ForegroundColor Cyan

# Test MCP Status Endpoint
Write-Host "Testing MCP Status Endpoint..." -ForegroundColor Green
try {
    $headers = @{
        "X-API-Key" = $API_KEY
    }
    $response = Invoke-RestMethod -Uri "$API_URL/mcp/status" -Method Get -Headers $headers
    $response | ConvertTo-Json
} catch {
    Write-Host "Error: $_" -ForegroundColor Red
}
Write-Host "====================================" -ForegroundColor Cyan

# Test Content Generation Endpoint
Write-Host "Testing Content Generation Endpoint..." -ForegroundColor Green
try {
    $headers = @{
        "Content-Type" = "application/json"
        "X-API-Key" = $API_KEY
    }
    $body = @{
        topic = "AI Technology"
        keywords = @("artificial intelligence", "machine learning")
        length = "short"
        tone = "professional"
    } | ConvertTo-Json
    
    $response = Invoke-RestMethod -Uri "$API_URL/api/blog-generator/generate" -Method Post -Headers $headers -Body $body
    $response | ConvertTo-Json
} catch {
    Write-Host "Error: $_" -ForegroundColor Red
}
Write-Host "====================================" -ForegroundColor Cyan

Write-Host "API Tests Completed." -ForegroundColor Yellow 