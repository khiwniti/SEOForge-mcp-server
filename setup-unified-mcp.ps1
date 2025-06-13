# SEO Forge Unified MCP Server Setup Script (PowerShell)
# Automates the complete setup and deployment process

param(
    [switch]$SkipTest,
    [switch]$SkipDeploy
)

Write-Host "🚀 SEO Forge Unified MCP Server Setup" -ForegroundColor Cyan
Write-Host "======================================" -ForegroundColor Cyan
Write-Host ""

function Write-Status {
    param($Message)
    Write-Host "[INFO] $Message" -ForegroundColor Blue
}

function Write-Success {
    param($Message)
    Write-Host "[SUCCESS] $Message" -ForegroundColor Green
}

function Write-Warning {
    param($Message)
    Write-Host "[WARNING] $Message" -ForegroundColor Yellow
}

function Write-Error {
    param($Message)
    Write-Host "[ERROR] $Message" -ForegroundColor Red
}

# Check prerequisites
Write-Status "Checking prerequisites..."

# Check Node.js
try {
    $nodeVersion = node --version
    $versionNumber = [int]($nodeVersion -replace 'v(\d+)\..*', '$1')
    
    if ($versionNumber -lt 18) {
        Write-Error "Node.js version 18+ is required. Current version: $nodeVersion"
        exit 1
    }
    
    Write-Success "Node.js $nodeVersion is installed"
} catch {
    Write-Error "Node.js is not installed. Please install Node.js 18+ and try again."
    exit 1
}

# Check npm
try {
    $npmVersion = npm --version
    Write-Success "npm $npmVersion is installed"
} catch {
    Write-Error "npm is not installed. Please install npm and try again."
    exit 1
}

# Install dependencies
Write-Status "Installing dependencies..."
Set-Location mcp-server-unified

try {
    npm install
    Write-Success "Dependencies installed successfully"
} catch {
    Write-Error "Failed to install dependencies"
    exit 1
}

# Setup environment file
Write-Status "Setting up environment configuration..."

if (-not (Test-Path .env)) {
    Copy-Item .env.example .env
    Write-Success "Environment file created from template"
    Write-Warning "Please edit .env file with your API keys before deployment"
} else {
    Write-Warning ".env file already exists, skipping creation"
}

# Build the project
Write-Status "Building the project..."

try {
    npm run build
    Write-Success "Project built successfully"
} catch {
    Write-Error "Build failed"
    exit 1
}

# Test locally (optional)
if (-not $SkipTest) {
    Write-Host ""
    $testLocal = Read-Host "Do you want to test the server locally first? (y/n)"
    
    if ($testLocal -eq 'y' -or $testLocal -eq 'Y') {
        Write-Status "Starting local development server..."
        Write-Warning "Server will start on http://localhost:3000"
        Write-Warning "Press Ctrl+C to stop the server and continue with deployment"
        
        Start-Process npm -ArgumentList "run", "dev" -NoNewWindow
        
        Read-Host "Press Enter when you're ready to continue with deployment..."
        
        # Stop npm processes
        Get-Process | Where-Object {$_.ProcessName -eq "node"} | Stop-Process -Force
        Write-Success "Local server stopped"
    }
}

# Check Vercel CLI
Write-Status "Checking Vercel CLI..."

try {
    vercel --version | Out-Null
    Write-Success "Vercel CLI is already installed"
} catch {
    Write-Warning "Vercel CLI not found. Installing..."
    
    try {
        npm install -g vercel
        Write-Success "Vercel CLI installed successfully"
    } catch {
        Write-Error "Failed to install Vercel CLI"
        exit 1
    }
}

# Login to Vercel
Write-Status "Checking Vercel authentication..."

try {
    $vercelUser = vercel whoami 2>$null
    Write-Success "Already logged in to Vercel as: $vercelUser"
} catch {
    Write-Warning "Not logged in to Vercel. Please login..."
    
    try {
        vercel login
        Write-Success "Successfully logged in to Vercel"
    } catch {
        Write-Error "Failed to login to Vercel"
        exit 1
    }
}

# Deploy to Vercel
if (-not $SkipDeploy) {
    Write-Host ""
    $deployNow = Read-Host "Do you want to deploy to Vercel now? (y/n)"
    
    if ($deployNow -eq 'y' -or $deployNow -eq 'Y') {
        Write-Status "Deploying to Vercel..."
        
        try {
            $deploymentOutput = vercel --prod 2>&1
            Write-Success "Deployment completed successfully!"
            
            # Extract deployment URL
            $deploymentUrl = ($deploymentOutput | Select-String -Pattern 'https://[^\s]*').Matches[0].Value
            
            if ($deploymentUrl) {
                Write-Host ""
                Write-Host "🎉 Your SEO Forge MCP Server is live!" -ForegroundColor Green
                Write-Host "==================================" -ForegroundColor Green
                Write-Host "🌐 Server URL: $deploymentUrl" -ForegroundColor Cyan
                Write-Host "🏥 Health Check: $deploymentUrl/health" -ForegroundColor Cyan
                Write-Host "💻 Client Interface: $deploymentUrl/client" -ForegroundColor Cyan
                Write-Host "📚 Documentation: $deploymentUrl/client/docs" -ForegroundColor Cyan
                Write-Host ""
            }
        } catch {
            Write-Error "Deployment failed"
            Write-Host $deploymentOutput
            exit 1
        }
    } else {
        Write-Warning "Skipping deployment. You can deploy later with: vercel --prod"
    }
}

# Environment variables setup
Write-Host ""
Write-Status "Environment Variables Setup"
Write-Host "============================" -ForegroundColor Cyan
Write-Host ""
Write-Warning "Don't forget to set up your environment variables in Vercel:"
Write-Host ""
Write-Host "Required variables:" -ForegroundColor Yellow
Write-Host "- GOOGLE_API_KEY (for Gemini AI)"
Write-Host "- OPENAI_API_KEY (for GPT-4, optional)"
Write-Host "- ANTHROPIC_API_KEY (for Claude, optional)"
Write-Host "- REPLICATE_API_TOKEN (for Flux/image generation, optional)"
Write-Host "- JWT_SECRET (for authentication)"
Write-Host "- DEFAULT_ADMIN_EMAIL (admin user email)"
Write-Host "- DEFAULT_ADMIN_PASSWORD (admin user password)"
Write-Host ""
Write-Host "Optional variables:" -ForegroundColor Yellow
Write-Host "- REDIS_URL (for production caching)"
Write-Host "- DATABASE_URL (for production data storage)"
Write-Host ""
Write-Host "You can set these in the Vercel dashboard or using the CLI:"
Write-Host "vercel env add GOOGLE_API_KEY" -ForegroundColor Gray
Write-Host ""

# WordPress integration guide
Write-Host ""
Write-Status "WordPress Integration"
Write-Host "====================" -ForegroundColor Cyan
Write-Host ""
Write-Host "To integrate with WordPress:"
Write-Host "1. Install the SEO Forge WordPress plugin"
Write-Host "2. Configure the MCP server URL in plugin settings"
Write-Host "3. Add your API key for authentication"
Write-Host "4. Test the connection"
Write-Host ""

# Final instructions
Write-Host ""
Write-Success "Setup completed successfully! 🎉"
Write-Host ""
Write-Host "Next steps:" -ForegroundColor Yellow
Write-Host "1. Set up environment variables in Vercel dashboard"
Write-Host "2. Test your deployment"
Write-Host "3. Configure WordPress plugin (if applicable)"
Write-Host "4. Start using the MCP server!"
Write-Host ""
Write-Host "Need help? Check the documentation or contact support."
Write-Host ""

# Cleanup
Set-Location ..

Write-Success "All done! Your SEO Forge MCP Server is ready to use."
