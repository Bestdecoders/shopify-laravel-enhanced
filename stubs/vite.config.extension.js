import { defineConfig } from "vite";
import { resolve } from "path";
import terser from "@rollup/plugin-terser";

export default defineConfig({
    build: {
        rollupOptions: {
            input: {
                // Main extension JavaScript entry point
                app_extension_js: resolve(__dirname, "resources/extension/js/app-extension.js"),
                // Main extension CSS entry point
                app_extension_css: resolve(__dirname, "resources/extension/css/app-extension.css"),
                // Additional extension scripts can be added here
                // theme_extension_js: resolve(__dirname, "resources/extension/js/theme-extension.js"),
            },
            output: {
                entryFileNames: (chunkInfo) => {
                    // Customize output file names based on entry point
                    if (chunkInfo.name === "app_extension_js") {
                        return "app-extension.js";
                    }
                    if (chunkInfo.name === "theme_extension_js") {
                        return "theme-extension.js";
                    }
                    return "[name].[hash].min.js";
                },
                assetFileNames: (assetInfo) => {
                    // Handle CSS and other assets
                    if (assetInfo.name.endsWith(".css")) {
                        return "[name].css";
                    }
                    return "[name].[ext]";
                },
                // Wrap output in IIFE for better isolation
                intro: '(function(){',
                outro: '})();',
                // Minify the output
                plugins: [terser({
                    compress: {
                        drop_console: true, // Remove console logs in production
                        drop_debugger: true, // Remove debugger statements
                    },
                    mangle: {
                        reserved: ['Shopify', 'window', 'document'] // Keep these global references
                    }
                })],
            },
        },
        // Output directory for Shopify app extensions
        outDir: resolve(__dirname, "extensions/shopify-enhanced-extension/assets"),
        minify: "terser",
        cssCodeSplit: true,
        // Generate sourcemaps for debugging
        sourcemap: process.env.NODE_ENV === 'development',
    },
    // Disable public directory copying
    publicDir: false,
    resolve: {
        alias: {
            "@": resolve(__dirname, "resources/extension"),
            "~": resolve(__dirname, "resources"),
        },
    },
    define: {
        // Define environment variables for the extension
        __EXTENSION_VERSION__: JSON.stringify(process.env.npm_package_version || '1.0.0'),
        __DEVELOPMENT__: JSON.stringify(process.env.NODE_ENV === 'development'),
    },
    server: {
        // Enable sourcemaps in development
        sourcemap: true,
        // Configure dev server for extension development
        host: 'localhost',
        port: 5174, // Different port from main app
    },
    // Optimize dependencies for extension context
    optimizeDeps: {
        include: [
            // Add any dependencies that should be pre-bundled
        ],
        exclude: [
            // Exclude Shopify globals that should remain external
            'Shopify',
        ],
    },
});