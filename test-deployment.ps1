# PowerShell script for testing Universal MCP Platform deployment
# Comprehensive testing suite for all components

param(
    [string]$BaseUrl = "http://localhost:8000",
    [string]$FrontendUrl = "http://localhost:3001",
    [string]$MCPServerUrl = "http://localhost:3000",
    [switch]$Verbose = $false
)

# Colors for output
$Red = "Red"
$Green = "Green"
$Yellow = "Yellow"
$Blue = "Cyan"

function Write-TestStatus {
    param([string]$Message)
    Write-Host "[TEST] $Message" -ForegroundColor $Blue
}

function Write-TestSuccess {
    param([string]$Message)
    Write-Host "[PASS] $Message" -ForegroundColor $Green
}

function Write-TestFailure {
    param([string]$Message)
    Write-Host "[FAIL] $Message" -ForegroundColor $Red
}

function Write-TestWarning {
    param([string]$Message)
    Write-Host "[WARN] $Message" -ForegroundColor $Yellow
}

function Test-Endpoint {
    param(
        [string]$Url,
        [string]$Description,
        [string]$Method = "GET",
        [hashtable]$Headers = @{},
        [string]$Body = $null
    )
    
    try {
        $params = @{
            Uri = $Url
            Method = $Method
            Headers = $Headers
            TimeoutSec = 30
        }
        
        if ($Body) {
            $params.Body = $Body
            $params.ContentType = "application/json"
        }
        
        if ($Verbose) {
            Write-TestStatus "Testing: $Description - $Url"
        }
        
        $response = Invoke-RestMethod @params
        Write-TestSuccess "$Description - Status: OK"
        
        if ($Verbose) {
            Write-Host "Response: $($response | ConvertTo-Json -Depth 2)" -ForegroundColor Gray
        }
        
        return $true
    }
    catch {
        Write-TestFailure "$Description - Error: $($_.Exception.Message)"
        return $false
    }
}

function Test-HealthEndpoints {
    Write-TestStatus "Testing Health Endpoints..."
    
    $tests = @(
        @{ Url = "$BaseUrl/health"; Description = "Backend Health Check" },
        @{ Url = "$MCPServerUrl/health"; Description = "MCP Server Health Check" },
        @{ Url = "$FrontendUrl/health"; Description = "Frontend Health Check" }
    )
    
    $passed = 0
    foreach ($test in $tests) {
        if (Test-Endpoint -Url $test.Url -Description $test.Description) {
            $passed++
        }
    }
    
    Write-TestStatus "Health Endpoints: $passed/$($tests.Count) passed"
    return $passed -eq $tests.Count
}

function Test-MCPServerEndpoints {
    Write-TestStatus "Testing MCP Server Endpoints..."
    
    $tests = @(
        @{ Url = "$BaseUrl/api/mcp-server/status"; Description = "MCP Server Status" },
        @{ Url = "$BaseUrl/api/mcp-server/tools"; Description = "Available Tools" },
        @{ Url = "$BaseUrl/api/mcp-server/industries"; Description = "Supported Industries" },
        @{ Url = "$BaseUrl/api/mcp-server/templates"; Description = "Industry Templates" }
    )
    
    $passed = 0
    foreach ($test in $tests) {
        if (Test-Endpoint -Url $test.Url -Description $test.Description) {
            $passed++
        }
    }
    
    Write-TestStatus "MCP Server Endpoints: $passed/$($tests.Count) passed"
    return $passed -eq $tests.Count
}

function Test-ContentGeneration {
    Write-TestStatus "Testing Content Generation..."
    
    $body = @{
        tool_name = "content_generation"
        parameters = @{
            topic = "AI in modern business"
            content_type = "blog_post"
            keywords = @("artificial intelligence", "business automation")
        }
        context = @{
            industry = "technology"
            language = "en"
        }
    } | ConvertTo-Json -Depth 3
    
    return Test-Endpoint -Url "$BaseUrl/api/mcp-server/execute-tool" -Description "Content Generation Tool" -Method "POST" -Body $body
}

function Test-SEOAnalysis {
    Write-TestStatus "Testing SEO Analysis..."
    
    $body = @{
        tool_name = "seo_analysis"
        parameters = @{
            url = "https://example.com"
            keywords = @("example", "test", "website")
        }
        context = @{
            industry = "general"
        }
    } | ConvertTo-Json -Depth 3
    
    return Test-Endpoint -Url "$BaseUrl/api/mcp-server/execute-tool" -Description "SEO Analysis Tool" -Method "POST" -Body $body
}

function Test-KeywordResearch {
    Write-TestStatus "Testing Keyword Research..."
    
    $body = @{
        tool_name = "keyword_research"
        parameters = @{
            seed_keyword = "artificial intelligence"
        }
        context = @{
            industry = "technology"
            language = "en"
        }
    } | ConvertTo-Json -Depth 3
    
    return Test-Endpoint -Url "$BaseUrl/api/mcp-server/execute-tool" -Description "Keyword Research Tool" -Method "POST" -Body $body
}

function Test-IndustryAnalysis {
    Write-TestStatus "Testing Industry Analysis..."
    
    $body = @{
        tool_name = "industry_analysis"
        parameters = @{
            industry = "technology"
            analysis_type = "overview"
        }
        context = @{
            industry = "technology"
        }
    } | ConvertTo-Json -Depth 3
    
    return Test-Endpoint -Url "$BaseUrl/api/mcp-server/execute-tool" -Description "Industry Analysis Tool" -Method "POST" -Body $body
}

function Test-BlogGenerator {
    Write-TestStatus "Testing Blog Generator..."
    
    $body = @{
        topic = "The Future of AI"
        keywords = @("artificial intelligence", "machine learning", "future technology")
        tone = "professional"
        length = "medium"
    } | ConvertTo-Json -Depth 2
    
    return Test-Endpoint -Url "$BaseUrl/api/blog-generator/generate" -Description "Blog Generator" -Method "POST" -Body $body
}

function Test-SEOAnalyzer {
    Write-TestStatus "Testing SEO Analyzer..."
    
    $body = @{
        url = "https://example.com"
        keywords = @("example", "test")
    } | ConvertTo-Json -Depth 2
    
    return Test-Endpoint -Url "$BaseUrl/api/seo-analyzer/analyze" -Description "SEO Analyzer" -Method "POST" -Body $body
}

function Test-FrontendPages {
    Write-TestStatus "Testing Frontend Pages..."
    
    $tests = @(
        @{ Url = "$FrontendUrl/"; Description = "Frontend Home Page" },
        @{ Url = "$FrontendUrl/dashboard"; Description = "Dashboard Page" },
        @{ Url = "$FrontendUrl/content-generator"; Description = "Content Generator Page" }
    )
    
    $passed = 0
    foreach ($test in $tests) {
        try {
            $response = Invoke-WebRequest -Uri $test.Url -TimeoutSec 30 -UseBasicParsing
            if ($response.StatusCode -eq 200) {
                Write-TestSuccess "$($test.Description) - Status: OK"
                $passed++
            } else {
                Write-TestFailure "$($test.Description) - Status: $($response.StatusCode)"
            }
        }
        catch {
            Write-TestFailure "$($test.Description) - Error: $($_.Exception.Message)"
        }
    }
    
    Write-TestStatus "Frontend Pages: $passed/$($tests.Count) passed"
    return $passed -eq $tests.Count
}

function Test-Performance {
    Write-TestStatus "Testing Performance..."
    
    $endpoints = @(
        "$BaseUrl/health",
        "$BaseUrl/api/mcp-server/status",
        "$FrontendUrl/"
    )
    
    $results = @()
    
    foreach ($endpoint in $endpoints) {
        try {
            $stopwatch = [System.Diagnostics.Stopwatch]::StartNew()
            $response = Invoke-RestMethod -Uri $endpoint -TimeoutSec 30
            $stopwatch.Stop()
            
            $responseTime = $stopwatch.ElapsedMilliseconds
            $results += @{
                Endpoint = $endpoint
                ResponseTime = $responseTime
                Status = "OK"
            }
            
            if ($responseTime -lt 1000) {
                Write-TestSuccess "Performance: $endpoint - ${responseTime}ms (Good)"
            } elseif ($responseTime -lt 3000) {
                Write-TestWarning "Performance: $endpoint - ${responseTime}ms (Acceptable)"
            } else {
                Write-TestFailure "Performance: $endpoint - ${responseTime}ms (Slow)"
            }
        }
        catch {
            $results += @{
                Endpoint = $endpoint
                ResponseTime = -1
                Status = "Error: $($_.Exception.Message)"
            }
            Write-TestFailure "Performance: $endpoint - Error: $($_.Exception.Message)"
        }
    }
    
    return $results
}

function Main {
    Write-Host "🧪 Universal MCP Platform - Deployment Testing Suite" -ForegroundColor $Blue
    Write-Host "============================================================" -ForegroundColor $Blue
    Write-Host ""
    Write-Host "Testing Configuration:" -ForegroundColor $Yellow
    Write-Host "  Backend URL: $BaseUrl"
    Write-Host "  Frontend URL: $FrontendUrl"
    Write-Host "  MCP Server URL: $MCPServerUrl"
    Write-Host "  Verbose Mode: $Verbose"
    Write-Host ""
    
    $testResults = @{
        HealthEndpoints = Test-HealthEndpoints
        MCPServerEndpoints = Test-MCPServerEndpoints
        ContentGeneration = Test-ContentGeneration
        SEOAnalysis = Test-SEOAnalysis
        KeywordResearch = Test-KeywordResearch
        IndustryAnalysis = Test-IndustryAnalysis
        BlogGenerator = Test-BlogGenerator
        SEOAnalyzer = Test-SEOAnalyzer
        FrontendPages = Test-FrontendPages
    }
    
    Write-Host ""
    Write-TestStatus "Running Performance Tests..."
    $performanceResults = Test-Performance
    
    Write-Host ""
    Write-Host "📊 Test Results Summary:" -ForegroundColor $Blue
    Write-Host "========================" -ForegroundColor $Blue
    
    $totalTests = 0
    $passedTests = 0
    
    foreach ($test in $testResults.GetEnumerator()) {
        $totalTests++
        if ($test.Value) {
            $passedTests++
            Write-Host "✅ $($test.Key): PASSED" -ForegroundColor $Green
        } else {
            Write-Host "❌ $($test.Key): FAILED" -ForegroundColor $Red
        }
    }
    
    Write-Host ""
    Write-Host "📈 Performance Results:" -ForegroundColor $Blue
    foreach ($result in $performanceResults) {
        if ($result.ResponseTime -gt 0) {
            Write-Host "  $($result.Endpoint): $($result.ResponseTime)ms" -ForegroundColor $Yellow
        } else {
            Write-Host "  $($result.Endpoint): $($result.Status)" -ForegroundColor $Red
        }
    }
    
    Write-Host ""
    $successRate = [math]::Round(($passedTests / $totalTests) * 100, 1)
    
    if ($successRate -eq 100) {
        Write-Host "🎉 All tests passed! ($passedTests/$totalTests) - Success Rate: $successRate%" -ForegroundColor $Green
        Write-Host "Your Universal MCP Platform is ready for production! 🚀" -ForegroundColor $Green
    } elseif ($successRate -ge 80) {
        Write-Host "⚠️  Most tests passed ($passedTests/$totalTests) - Success Rate: $successRate%" -ForegroundColor $Yellow
        Write-Host "Platform is mostly functional but some issues need attention." -ForegroundColor $Yellow
    } else {
        Write-Host "❌ Many tests failed ($passedTests/$totalTests) - Success Rate: $successRate%" -ForegroundColor $Red
        Write-Host "Platform needs significant fixes before production deployment." -ForegroundColor $Red
    }
    
    Write-Host ""
    Write-Host "💡 Next Steps:" -ForegroundColor $Blue
    Write-Host "  1. Fix any failing tests"
    Write-Host "  2. Optimize performance for slow endpoints"
    Write-Host "  3. Deploy to Vercel using: .\deploy-vercel.ps1 deploy"
    Write-Host "  4. Run tests against production deployment"
    Write-Host "  5. Install and configure WordPress plugin"
}

# Run the main function
Main
