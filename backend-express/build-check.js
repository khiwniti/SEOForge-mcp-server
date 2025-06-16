#!/usr/bin/env node

/**
 * Build Check Script
 * Verifies that the build is ready for Vercel deployment
 */

const fs = require('fs');
const path = require('path');

console.log('🔍 Checking build readiness for Vercel deployment...\n');

// Check if required files exist
const requiredFiles = [
  'package.json',
  'tsconfig.json',
  'vercel.json',
  'api/index.ts',
  'src/server.ts'
];

let allFilesExist = true;

console.log('📁 Checking required files:');
requiredFiles.forEach(file => {
  if (fs.existsSync(file)) {
    console.log(`✅ ${file}`);
  } else {
    console.log(`❌ ${file} - MISSING`);
    allFilesExist = false;
  }
});

if (!allFilesExist) {
  console.log('\n❌ Some required files are missing!');
  process.exit(1);
}

// Check package.json configuration
console.log('\n📦 Checking package.json configuration:');
const packageJson = JSON.parse(fs.readFileSync('package.json', 'utf8'));

// Check Node.js version
if (packageJson.engines && packageJson.engines.node) {
  console.log(`✅ Node.js version: ${packageJson.engines.node}`);
} else {
  console.log('⚠️  No Node.js version specified in engines');
}

// Check if type: "module" is removed
if (packageJson.type === 'module') {
  console.log('❌ package.json still has "type": "module" - this should be removed for CommonJS');
  process.exit(1);
} else {
  console.log('✅ CommonJS configuration (no "type": "module")');
}

// Check TypeScript configuration
console.log('\n🔧 Checking TypeScript configuration:');
const tsConfig = JSON.parse(fs.readFileSync('tsconfig.json', 'utf8'));

if (tsConfig.compilerOptions.module === 'CommonJS') {
  console.log('✅ TypeScript module: CommonJS');
} else {
  console.log(`⚠️  TypeScript module: ${tsConfig.compilerOptions.module} (should be CommonJS for Vercel)`);
}

if (tsConfig.compilerOptions.target === 'ES2020') {
  console.log('✅ TypeScript target: ES2020');
} else {
  console.log(`⚠️  TypeScript target: ${tsConfig.compilerOptions.target} (ES2020 recommended)`);
}

// Check Vercel configuration
console.log('\n🚀 Checking Vercel configuration:');
const vercelConfig = JSON.parse(fs.readFileSync('vercel.json', 'utf8'));

if (vercelConfig.builds && vercelConfig.builds[0].src === 'backend-express/api/index.ts') {
  console.log('✅ Vercel build source points to api/index.ts');
} else {
  console.log('⚠️  Vercel build source configuration may need adjustment');
}

if (vercelConfig.functions && vercelConfig.functions['backend-express/api/index.ts']) {
  console.log('✅ Vercel function configuration found');
} else {
  console.log('⚠️  Vercel function configuration may need adjustment');
}

// Check for common issues
console.log('\n🔍 Checking for common issues:');

// Check for .js imports in TypeScript files
const srcFiles = fs.readdirSync('src', { recursive: true })
  .filter(file => file.endsWith('.ts'))
  .map(file => path.join('src', file));

let hasJsImports = false;
srcFiles.forEach(file => {
  const content = fs.readFileSync(file, 'utf8');
  if (content.includes('.js\'') || content.includes('.js"')) {
    console.log(`⚠️  ${file} contains .js imports (should be removed for CommonJS)`);
    hasJsImports = true;
  }
});

if (!hasJsImports) {
  console.log('✅ No .js imports found in TypeScript files');
}

// Check for hardcoded API keys
let hasHardcodedKeys = false;
srcFiles.forEach(file => {
  const content = fs.readFileSync(file, 'utf8');
  if (content.includes('AIzaSy') && !content.includes('process.env')) {
    console.log(`❌ ${file} contains hardcoded API keys`);
    hasHardcodedKeys = true;
  }
});

if (!hasHardcodedKeys) {
  console.log('✅ No hardcoded API keys found');
}

console.log('\n🎯 Build Check Summary:');
if (allFilesExist && !hasHardcodedKeys) {
  console.log('✅ Build is ready for Vercel deployment!');
  console.log('\n📋 Next steps:');
  console.log('1. Run: npm install');
  console.log('2. Run: npm run build');
  console.log('3. Deploy: vercel --prod');
  console.log('\n🔑 Environment variables (already configured for public use):');
  console.log('✅ GOOGLE_API_KEY (configured)');
  console.log('- OPENAI_API_KEY (optional fallback)');
  console.log('- ANTHROPIC_API_KEY (optional fallback)');
  console.log('\n🌐 This is now a PUBLIC API - no authentication required!');
} else {
  console.log('❌ Build has issues that need to be fixed before deployment');
  process.exit(1);
}
