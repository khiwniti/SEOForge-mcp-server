# PowerShell script for Vercel deployment on Windows
# Universal MCP Server Platform - Vercel Deployment Script

param(
    [string]$Command = "deploy",
    [switch]$Production = $false
)

# Colors for output
$Red = "Red"
$Green = "Green"
$Yellow = "Yellow"
$Blue = "Cyan"

function Write-Status {
    param([string]$Message)
    Write-Host "[INFO] $Message" -ForegroundColor $Blue
}

function Write-Success {
    param([string]$Message)
    Write-Host "[SUCCESS] $Message" -ForegroundColor $Green
}

function Write-Warning {
    param([string]$Message)
    Write-Host "[WARNING] $Message" -ForegroundColor $Yellow
}

function Write-Error {
    param([string]$Message)
    Write-Host "[ERROR] $Message" -ForegroundColor $Red
}

function Test-Command {
    param([string]$CommandName)
    return Get-Command $CommandName -ErrorAction SilentlyContinue
}

function Test-Prerequisites {
    Write-Status "Checking prerequisites..."
    
    if (-not (Test-Command "node")) {
        Write-Error "Node.js is not installed. Please install Node.js first."
        exit 1
    }
    
    if (-not (Test-Command "npm")) {
        Write-Error "npm is not installed. Please install npm first."
        exit 1
    }
    
    if (-not (Test-Command "git")) {
        Write-Error "Git is not installed. Please install Git first."
        exit 1
    }
    
    Write-Success "All prerequisites are installed."
}

function Setup-Environment {
    Write-Status "Setting up environment for Vercel..."
    
    # Install Vercel CLI if not present
    if (-not (Test-Command "vercel")) {
        Write-Status "Installing Vercel CLI..."
        npm install -g vercel
    }
    
    # Check if .env.production exists
    if (-not (Test-Path ".env.production")) {
        Write-Warning ".env.production file not found. Creating from template..."
        if (Test-Path ".env.example") {
            Copy-Item ".env.example" ".env.production"
            Write-Warning "Please edit .env.production with your production values."
        }
    }
    
    Write-Success "Environment setup completed."
}

function Fix-LineEndings {
    Write-Status "Fixing line endings for Windows..."
    
    # Configure Git for Windows
    git config core.autocrlf true
    git config core.safecrlf false
    git config core.eol crlf
    
    # Apply .gitattributes
    if (Test-Path ".gitattributes") {
        git add .gitattributes
        git commit -m "Add .gitattributes for line ending handling" -q 2>$null
    }
    
    Write-Success "Line endings configured."
}

function Build-Frontend {
    Write-Status "Building frontend..."
    
    Set-Location "frontend"
    
    # Install dependencies
    Write-Status "Installing frontend dependencies..."
    npm install
    
    # Build for production
    Write-Status "Building frontend for production..."
    npm run build
    
    Set-Location ".."
    
    Write-Success "Frontend build completed."
}

function Deploy-To-Vercel {
    Write-Status "Deploying to Vercel..."
    
    if ($Production) {
        Write-Status "Deploying to production..."
        vercel --prod
    } else {
        Write-Status "Deploying to preview..."
        vercel
    }
    
    Write-Success "Deployment completed!"
}

function Show-Deployment-Info {
    Write-Success "🚀 Universal MCP Server Platform deployed to Vercel!"
    Write-Host ""
    Write-Host "📊 Next steps:" -ForegroundColor $Blue
    Write-Host "  1. Configure environment variables in Vercel dashboard"
    Write-Host "  2. Set up your database (Vercel Postgres recommended)"
    Write-Host "  3. Configure your domain settings"
    Write-Host "  4. Install the WordPress plugin"
    Write-Host ""
    Write-Host "🔧 Important Vercel Environment Variables:" -ForegroundColor $Yellow
    Write-Host "  • OPENAI_API_KEY"
    Write-Host "  • ANTHROPIC_API_KEY"
    Write-Host "  • GOOGLE_AI_API_KEY"
    Write-Host "  • MCP_API_KEY"
    Write-Host "  • JWT_SECRET"
    Write-Host "  • DATABASE_URL"
    Write-Host "  • REDIS_URL"
    Write-Host ""
    Write-Host "📝 Useful commands:" -ForegroundColor $Green
    Write-Host "  • View logs: vercel logs"
    Write-Host "  • List deployments: vercel ls"
    Write-Host "  • Remove deployment: vercel rm"
    Write-Host "  • Open dashboard: vercel dashboard"
}

function Main {
    Write-Host "🌟 Universal MCP Server Platform - Vercel Deployment" -ForegroundColor $Blue
    Write-Host "======================================================" -ForegroundColor $Blue
    Write-Host ""
    
    switch ($Command.ToLower()) {
        "deploy" {
            Test-Prerequisites
            Setup-Environment
            Fix-LineEndings
            Build-Frontend
            Deploy-To-Vercel
            Show-Deployment-Info
        }
        "build" {
            Test-Prerequisites
            Build-Frontend
            Write-Success "Build completed successfully."
        }
        "setup" {
            Test-Prerequisites
            Setup-Environment
            Fix-LineEndings
            Write-Success "Setup completed successfully."
        }
        "logs" {
            vercel logs
        }
        "dashboard" {
            vercel dashboard
        }
        "status" {
            vercel ls
        }
        "help" {
            Write-Host "Usage: .\deploy-vercel.ps1 [command] [-Production]"
            Write-Host ""
            Write-Host "Commands:"
            Write-Host "  deploy     Deploy the entire platform (default)"
            Write-Host "  build      Build frontend only"
            Write-Host "  setup      Setup environment and dependencies"
            Write-Host "  logs       View deployment logs"
            Write-Host "  dashboard  Open Vercel dashboard"
            Write-Host "  status     Show deployment status"
            Write-Host "  help       Show this help message"
            Write-Host ""
            Write-Host "Flags:"
            Write-Host "  -Production  Deploy to production (default: preview)"
        }
        default {
            Write-Error "Unknown command: $Command"
            Write-Host "Use '.\deploy-vercel.ps1 help' for usage information."
            exit 1
        }
    }
}

# Run the main function
Main
