#!/usr/bin/env node

/**
 * Simple Build Script for Vercel Deployment
 * Avoids complex dependencies that cause build issues
 */

const { execSync } = require('child_process');
const fs = require('fs');
const path = require('path');

console.log('🚀 Starting simple build process...');

// Clean dist directory
console.log('🧹 Cleaning dist directory...');
try {
  if (fs.existsSync('dist')) {
    fs.rmSync('dist', { recursive: true, force: true });
  }
  console.log('✅ Dist directory cleaned');
} catch (error) {
  console.log('⚠️  Clean failed, continuing...', error.message);
}

// Create dist directory
fs.mkdirSync('dist', { recursive: true });

// Compile TypeScript
console.log('🔨 Compiling TypeScript...');
try {
  execSync('npx tsc --project tsconfig.json', { 
    stdio: 'inherit',
    timeout: 120000 // 2 minutes timeout
  });
  console.log('✅ TypeScript compilation completed');
} catch (error) {
  console.error('❌ TypeScript compilation failed:', error.message);
  process.exit(1);
}

// Verify build output
console.log('🔍 Verifying build output...');
const requiredFiles = [
  'dist/server.js',
  'dist/services/mcp-service-manager.js',
  'dist/routes/v1.js'
];

let allFilesExist = true;
requiredFiles.forEach(file => {
  if (fs.existsSync(file)) {
    console.log(`✅ ${file}`);
  } else {
    console.log(`❌ ${file} - MISSING`);
    allFilesExist = false;
  }
});

if (!allFilesExist) {
  console.error('❌ Build verification failed - some files are missing');
  process.exit(1);
}

// Copy package.json to dist for reference
try {
  fs.copyFileSync('package.json', 'dist/package.json');
  console.log('✅ Package.json copied to dist');
} catch (error) {
  console.log('⚠️  Could not copy package.json:', error.message);
}

console.log('🎉 Build completed successfully!');
console.log('📦 Build artifacts:');
console.log('   - dist/server.js (main entry point)');
console.log('   - dist/services/ (service modules)');
console.log('   - dist/routes/ (route modules)');
console.log('   - dist/middleware/ (middleware modules)');

console.log('\n🚀 Ready for Vercel deployment!');
console.log('Next steps:');
console.log('1. Commit your changes');
console.log('2. Push to GitHub');
console.log('3. Deploy with: vercel --prod');
