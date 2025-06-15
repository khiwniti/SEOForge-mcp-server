# Dependency Cleanup Plan

## Current State
The repository has multiple dependency files scattered across different directories:
- Root: package.json, requirements.txt, requirements-consolidated.txt, deployment-requirements.txt
- Backend (FastAPI): pyproject.toml, requirements.txt
- Backend-Express: package.json
- Frontend: package.json
- Multiple MCP server directories with their own package.json files

## Cleanup Strategy

### 1. Primary Backend: Express (Node.js)
- Keep and optimize: `/backend-express/package.json`
- Remove Python dependencies since we're switching to Express

### 2. Frontend: React + Vite
- Keep: `/frontend/package.json`
- Clean up and optimize dependencies

### 3. Root Level
- Keep: `package.json` (for Cloudflare Workers)
- Remove: Python requirements files (since we're using Express)

### 4. Deprecated/Unused
- Archive: `/backend/` (FastAPI - no longer needed)
- Clean up: Multiple MCP server directories (consolidate)

## Actions Taken
1. ✅ Created cleanup plan
2. ✅ Removed redundant Python dependencies (requirements.txt, requirements-consolidated.txt, deployment-requirements.txt)
3. ✅ Optimized Express backend dependencies
4. ✅ Set up Vercel deployment for Express backend
5. ✅ Created Vercel configuration and API entry point
6. ✅ Added deployment script (deploy-vercel.sh)
7. ✅ Updated documentation

## Files Removed
- `/requirements.txt` (redundant - using Express)
- `/requirements-consolidated.txt` (redundant - using Express)
- `/deployment-requirements.txt` (redundant - using Express)

## Files Added/Modified
- `/backend-express/vercel.json` (Vercel configuration)
- `/backend-express/api/index.ts` (Vercel entry point)
- `/backend-express/deploy-vercel.sh` (Deployment script)
- `/backend-express/package.json` (Added Vercel scripts)
- `/backend-express/src/server.ts` (Modified for Vercel compatibility)
- `/DEPLOYMENT_GUIDE_EXPRESS.md` (New deployment guide)

## Current Structure
```
SEOForge MCP Server (Clean)
├── backend-express/          # Primary Express backend
├── frontend/                 # React frontend
├── cloudflare-worker.js      # Alternative Cloudflare deployment
├── package.json              # Root (Cloudflare Workers)
└── docs/                     # Documentation
```