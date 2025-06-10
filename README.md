# 🚀 Universal MCP Server Web Platform

A production-ready, universal Model Context Protocol (MCP) server web application that provides AI-powered tools and services for any industry, with extensible architecture and comprehensive management capabilities.

## 🎯 **Project Overview**

This comprehensive platform delivers:
- **Universal MCP Server**: Multi-industry AI orchestration with extensible context providers
- **Web Dashboard**: Full-featured React-based management interface
- **Plugin System**: Extensible WordPress and other CMS integrations
- **Multi-Language Support**: Global localization capabilities
- **Industry Templates**: Pre-built templates for various industries (e-commerce, healthcare, finance, etc.)

## 🏗️ **Architecture**

```
Universal MCP Server Platform
├── MCP Server (Node.js/TypeScript)
│   ├── Universal Context Engine
│   ├── Multi-Model AI Orchestration
│   ├── Industry-Specific Providers
│   ├── Plugin Management System
│   └── Real-time Analytics
├── Backend API (Python/FastAPI)
│   ├── Content Generation
│   ├── Data Analytics
│   ├── User Management
│   ├── Industry Templates
│   └── Integration Hub
├── Frontend Dashboard (React/TypeScript)
│   ├── Universal Dashboard
│   ├── Industry Workspaces
│   ├── Plugin Marketplace
│   ├── Analytics & Reporting
│   └── User Management
├── Plugin Ecosystem
│   ├── WordPress Plugin
│   ├── Shopify Integration
│   ├── Custom API Clients
│   └── Third-party Connectors
└── Infrastructure
    ├── Docker Containers
    ├── Redis Caching
    ├── PostgreSQL Database
    └── Cloud Deployment
```

## 🚀 **Quick Start**

### Prerequisites
- Docker & Docker Compose
- Node.js 18+ (for local development)
- Python 3.11+ (for local development)
- Git

### 1. Clone and Setup
```bash
git clone <repository-url>
cd wordpress-plugin-with-mcp-server

# Copy environment template
cp .env.example .env

# Edit .env with your API keys
nano .env
```

### 2. Environment Variables
Create a `.env` file with the following:
```env
# API Keys
OPENAI_API_KEY=your_openai_api_key
ANTHROPIC_API_KEY=your_anthropic_api_key
GOOGLE_AI_API_KEY=your_google_ai_api_key

# MCP Server
MCP_API_KEY=your_secure_mcp_api_key
JWT_SECRET=your_jwt_secret

# Database
DATABASE_URL=postgresql://postgres:password@localhost:5432/universal_mcp
REDIS_URL=redis://localhost:6379
REDIS_PASSWORD=redis_password

# Monitoring
GRAFANA_PASSWORD=admin
```

### 3. Deploy with Docker
```bash
# Build and start all services
docker-compose up -d

# Check service status
docker-compose ps

# View logs
docker-compose logs -f mcp-server
```

### 4. Access the Platform
- **Frontend Dashboard**: http://localhost:3001
- **Backend API**: http://localhost:8000
- **MCP Server**: http://localhost:3000
- **API Documentation**: http://localhost:8000/docs
- **Grafana Monitoring**: http://localhost:3002

## 🔧 **Development Setup**

### Backend Development
```bash
cd backend
python -m venv venv
source venv/bin/activate  # On Windows: venv\Scripts\activate
pip install -r requirements.txt
uvicorn main:app --reload --port 8000
```

### Frontend Development
```bash
cd frontend
npm install
npm run dev
```

### MCP Server Development
```bash
cd mcp-server
npm install
npm run dev
```

## Stack

- React+Typescript frontend with `yarn` as package manager.
- Python FastAPI server with `uv` as package manager.

## Quickstart

1. Install dependencies:

```bash
make
```

2. Start the backend and frontend servers in separate terminals:

```bash
make run-backend
make run-frontend
```

## Gotchas

The backend server runs on port 8000 and the frontend development server runs on port 5173. The frontend Vite server proxies API requests to the backend on port 8000.

Visit <http://localhost:5173> to view the application.
