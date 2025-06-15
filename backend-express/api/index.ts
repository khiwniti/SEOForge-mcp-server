/**
 * Vercel Serverless Function Entry Point
 * This file exports the Express app for Vercel deployment
 */

import { app } from '../src/server.js';

// Export the Express app as a Vercel serverless function
export default app;