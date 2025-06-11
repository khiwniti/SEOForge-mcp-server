import react from "@vitejs/plugin-react";
import "dotenv/config";
import path from "node:path";
import { defineConfig, splitVendorChunkPlugin } from "vite";
import injectHTML from "vite-plugin-html-inject";
import tsConfigPaths from "vite-tsconfig-paths";

const buildVariables = () => {
	const isProduction = process.env.NODE_ENV === "production";
	const apiUrl = isProduction 
		? process.env.VITE_API_URL || "https://seoforge-mcp-platform.vercel.app"
		: "http://localhost:8000";

	const defines: Record<string, string> = {
		__APP_ID__: JSON.stringify("seoforge-mcp"),
		__API_PATH__: JSON.stringify(""),
		__API_URL__: JSON.stringify(apiUrl),
		__WS_API_URL__: JSON.stringify(apiUrl.replace("http", "ws")),
		__APP_BASE_PATH__: JSON.stringify("/"),
		__APP_TITLE__: JSON.stringify("SEOForge MCP Platform"),
		__APP_FAVICON_LIGHT__: JSON.stringify("/favicon.ico"),
		__APP_FAVICON_DARK__: JSON.stringify("/favicon.ico"),
		__APP_DEPLOY_USERNAME__: JSON.stringify(""),
		__APP_DEPLOY_APPNAME__: JSON.stringify("seoforge-mcp"),
		__APP_DEPLOY_CUSTOM_DOMAIN__: JSON.stringify(""),
		__FIREBASE_CONFIG__: JSON.stringify("{}"),
	};

	return defines;
};

// https://vite.dev/config/
export default defineConfig({
	define: buildVariables(),
	plugins: [react(), splitVendorChunkPlugin(), tsConfigPaths(), injectHTML()],
	build: {
		outDir: "dist",
		sourcemap: false,
		rollupOptions: {
			output: {
				manualChunks: (id) => {
					if (id.includes('node_modules')) {
						if (id.includes('react') || id.includes('react-dom')) {
							return 'vendor';
						}
						if (id.includes('react-router')) {
							return 'router';
						}
						return 'vendor';
					}
				},
			},
		},
	},
	server: {
		host: "0.0.0.0",
		port: 3000,
		proxy: {
			"/api": {
				target: "http://127.0.0.1:8000",
				changeOrigin: true,
			},
			"/mcp-server": {
				target: "http://127.0.0.1:8000",
				changeOrigin: true,
			},
			"/wordpress": {
				target: "http://127.0.0.1:8000",
				changeOrigin: true,
			},
			"/health": {
				target: "http://127.0.0.1:8000",
				changeOrigin: true,
			},
		},
	},
	resolve: {
		alias: {
			"@": path.resolve(__dirname, "./src"),
		},
	},
});
