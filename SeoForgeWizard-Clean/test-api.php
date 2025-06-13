<?php
/**
 * SEO-Forge API Testing Interface
 * This file tests all API endpoints and identifies issues
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// API configuration
$api_base_url = 'https://seoforge-mcp-server.onrender.com';
$timeout = 60; // Extended timeout for sleeping server

/**
 * Make HTTP request to API
 */
function make_api_request($endpoint, $method = 'GET', $data = null, $timeout = 60) {
    global $api_base_url;
    
    $url = $api_base_url . $endpoint;
    $ch = curl_init();
    
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => $timeout,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Accept: application/json'
        ],
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_FOLLOWLOCATION => true
    ]);
    
    if ($data && in_array($method, ['POST', 'PUT', 'PATCH'])) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    $info = curl_getinfo($ch);
    
    curl_close($ch);
    
    return [
        'success' => $http_code >= 200 && $http_code < 300,
        'http_code' => $http_code,
        'response' => $response,
        'error' => $error,
        'info' => $info
    ];
}

/**
 * Test results storage
 */
$test_results = [];

/**
 * Run API test
 */
function run_test($name, $endpoint, $method = 'GET', $data = null, $expected_code = 200) {
    global $test_results;
    
    echo "<div class='test-item'>";
    echo "<h3>Testing: $name</h3>";
    echo "<p><strong>Endpoint:</strong> $method $endpoint</p>";
    
    if ($data) {
        echo "<p><strong>Data:</strong> " . json_encode($data, JSON_PRETTY_PRINT) . "</p>";
    }
    
    $start_time = microtime(true);
    $result = make_api_request($endpoint, $method, $data);
    $end_time = microtime(true);
    $duration = round(($end_time - $start_time) * 1000, 2);
    
    $status_class = $result['success'] ? 'success' : 'error';
    
    echo "<div class='result $status_class'>";
    echo "<p><strong>Status:</strong> " . ($result['success'] ? 'SUCCESS' : 'FAILED') . "</p>";
    echo "<p><strong>HTTP Code:</strong> {$result['http_code']}</p>";
    echo "<p><strong>Duration:</strong> {$duration}ms</p>";
    
    if ($result['error']) {
        echo "<p><strong>Error:</strong> {$result['error']}</p>";
    }
    
    if ($result['response']) {
        $decoded = json_decode($result['response'], true);
        if ($decoded) {
            echo "<p><strong>Response:</strong></p>";
            echo "<pre>" . json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "</pre>";
        } else {
            echo "<p><strong>Raw Response:</strong></p>";
            echo "<pre>" . htmlspecialchars(substr($result['response'], 0, 500)) . "</pre>";
        }
    }
    
    echo "</div>";
    echo "</div>";
    
    $test_results[$name] = $result;
    
    return $result;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SEO-Forge API Testing</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .header {
            background: #007cba;
            color: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .test-item {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .result {
            padding: 15px;
            border-radius: 4px;
            margin-top: 10px;
        }
        .result.success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        .result.error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        pre {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 4px;
            overflow-x: auto;
            font-size: 12px;
        }
        .summary {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .loading {
            text-align: center;
            padding: 20px;
        }
        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #007cba;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 2s linear infinite;
            margin: 0 auto;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>SEO-Forge API Testing Interface</h1>
        <p>Testing all endpoints of the FastAPI server at: <?php echo $api_base_url; ?></p>
    </div>
    
    <?php if (!isset($_GET['run'])): ?>
    <div class="summary">
        <h2>Ready to Test</h2>
        <p>This will systematically test all API endpoints to identify issues:</p>
        <ul>
            <li>Health check endpoint</li>
            <li>Blog content generation</li>
            <li>SEO title generation</li>
            <li>Meta description generation</li>
            <li>Image generation</li>
            <li>SEO content analysis</li>
            <li>Keyword suggestions</li>
            <li>Chatbot responses</li>
        </ul>
        <a href="?run=1" style="display: inline-block; background: #007cba; color: white; padding: 15px 30px; text-decoration: none; border-radius: 4px; margin-top: 15px;">Start API Testing</a>
    </div>
    <?php else: ?>
    
    <div class="loading">
        <div class="spinner"></div>
        <p>Testing API endpoints... This may take a few minutes as the server wakes up.</p>
    </div>
    
    <?php
    // Run all tests
    echo "<h2>Test Results</h2>";
    
    // Test 1: Server Status
    run_test(
        "Server Status", 
        "/universal-mcp/status", 
        "GET"
    );
    
    // Test 2: Documentation
    run_test(
        "API Documentation", 
        "/docs", 
        "GET"
    );
    
    // Test 3: OpenAPI Schema
    run_test(
        "OpenAPI Schema", 
        "/openapi.json", 
        "GET"
    );
    
    // Test 4: Blog Content Generation with Images
    run_test(
        "Blog Content Generation with Images", 
        "/universal-mcp/generate-blog-with-images", 
        "POST", 
        [
            "content_type" => "blog_post",
            "topic" => "WordPress SEO Best Practices",
            "keywords" => ["SEO", "WordPress", "optimization"],
            "length" => "medium",
            "language" => "en",
            "tone" => "professional",
            "industry" => "general",
            "include_images" => true,
            "image_count" => 3,
            "image_style" => "professional"
        ]
    );
    
    // Test 5: Generate from Keywords Only
    run_test(
        "Generate from Keywords Only", 
        "/universal-mcp/generate-from-keywords?language=en&tone=professional&length=medium&industry=general&include_images=true", 
        "POST", 
        ["WordPress SEO", "optimization", "content marketing"]
    );
    
    // Test 6: Basic Image Generation
    run_test(
        "Basic Image Generation", 
        "/universal-mcp/generate-image", 
        "POST", 
        [
            "prompt" => "WordPress website with SEO optimization elements",
            "style" => "professional",
            "size" => "1024x1024",
            "count" => 1
        ]
    );
    
    // Test 7: Flux Image Generation
    run_test(
        "Flux Image Generation", 
        "/universal-mcp/generate-flux-image", 
        "POST", 
        [
            "prompt" => "Modern WordPress dashboard with SEO analytics",
            "model" => "flux-schnell",
            "width" => 1024,
            "height" => 1024,
            "steps" => 4,
            "guidance" => 3.5
        ]
    );
    
    // Test 8: SEO Analysis
    run_test(
        "SEO Content Analysis", 
        "/universal-mcp/analyze-seo", 
        "POST", 
        [
            "content" => "This is a sample blog post about WordPress SEO optimization techniques and best practices. SEO is important for website visibility.",
            "target_keyword" => "WordPress SEO",
            "url" => "https://example.com"
        ]
    );
    
    // Test 9: Website Analysis
    run_test(
        "Website Analysis", 
        "/universal-mcp/analyze-website", 
        "POST", 
        [
            "url" => "https://wordpress.org",
            "analysis_type" => "simple"
        ]
    );
    
    // Test 10: Chatbot Response
    run_test(
        "Chatbot Response", 
        "/universal-mcp/chatbot", 
        "POST", 
        [
            "message" => "How can I improve my WordPress website SEO?",
            "context" => [],
            "language" => "en",
            "website_url" => "https://example.com"
        ]
    );
    
    // Test 11: Get Flux Models
    run_test(
        "Get Flux Models", 
        "/universal-mcp/flux-models", 
        "GET"
    );
    
    // Test 12: Enhance Flux Prompt
    run_test(
        "Enhance Flux Prompt", 
        "/universal-mcp/enhance-flux-prompt", 
        "POST", 
        [
            "prompt" => "WordPress SEO dashboard"
        ]
    );
    
    // Summary
    $total_tests = count($test_results);
    $successful_tests = array_sum(array_map(function($result) { return $result['success'] ? 1 : 0; }, $test_results));
    $failed_tests = $total_tests - $successful_tests;
    
    echo "<div class='summary'>";
    echo "<h2>Test Summary</h2>";
    echo "<p><strong>Total Tests:</strong> $total_tests</p>";
    echo "<p><strong>Successful:</strong> $successful_tests</p>";
    echo "<p><strong>Failed:</strong> $failed_tests</p>";
    
    if ($failed_tests > 0) {
        echo "<h3>Issues Identified:</h3>";
        echo "<ul>";
        foreach ($test_results as $name => $result) {
            if (!$result['success']) {
                echo "<li><strong>$name:</strong> HTTP {$result['http_code']} - {$result['error']}</li>";
            }
        }
        echo "</ul>";
    }
    
    echo "<a href='?' style='display: inline-block; background: #666; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; margin-top: 15px;'>Run Tests Again</a>";
    echo "</div>";
    ?>
    
    <?php endif; ?>
</body>
</html>