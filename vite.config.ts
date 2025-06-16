import type {
    UserConfig,
    BuildOptions,
    ServerOptions,
    CSSOptions, // Import CSSOptions for explicit typing
} from "vite";
import laravel from "laravel-vite-plugin";
import vue from "@vitejs/plugin-vue";
// import { watch } from "vite-plugin-watch"; // Keep watch for route generation, but consider alternatives if it becomes a bottleneck
import { VitePWA } from "vite-plugin-pwa";
import { templateCompilerOptions } from "@tresjs/core"; // Keep for TresJS
import path from "path";
import basicSsl from "@vitejs/plugin-basic-ssl"; // Keep for HTTPS dev
import viteImagemin from "vite-plugin-imagemin";
import { compression, defineAlgorithm } from "vite-plugin-compression2";
import vueDevTools from "vite-plugin-vue-devtools"; // Dev only, disable in production

// --- Explicitly type sub-configs for better TS performance and clarity ---

const buildConfig: BuildOptions = {
    target: "esnext",
    minify: "esbuild", // 'esbuild' is much faster than 'terser' for minification
    sourcemap: false, // Set to false for faster production builds. Enable for dev debugging.
    cssCodeSplit: true,
    rollupOptions: {
        output: {
            // Manual chunks help with caching and smaller initial loads
            manualChunks(id: string) {
                if (id.includes("node_modules")) {
                    const packageName = id
                        .toString()
                        .split("node_modules/")[1]
                        .split("/")[0];
                    // Group common large dependencies together, e.g., Vue, TresJS
                    if (
                        packageName.includes("vue") ||
                        packageName.includes("@tresjs")
                    ) {
                        return "vendor-vue-tresjs";
                    }
                    // Return other node_modules as separate chunks
                    return `vendor-${packageName}`;
                }
                // Group common Laravel assets if they get too large
                if (id.includes("resources/js/Components")) {
                    return "app-components";
                }
            },
            // Reduce asset file names hash length for a slightly faster build
            assetFileNames: "assets/[name]-[hash:7][extname]",
            chunkFileNames: "assets/[name]-[hash:7].js",
            entryFileNames: "assets/[name]-[hash:7].js",
        },
    },
    terserOptions: {
        // terserOptions are only used if `minify: 'terser'`
        compress: {
            drop_console: true,
            drop_debugger: true,
        },
        mangle: { reserved: ["ClassName"] },
    },
    assetsInlineLimit: 4096, // Default is 4kb, adjust if you have many small images
};

const serverConfig: ServerOptions = {
    host: true,
    cors: true,
    strictPort: true,
    hmr: {
        host: "localhost",
        protocol: "ws",
    },
    watch: {
        usePolling: false,
        // Adjusted ignored paths to only watch what's necessary
        ignored: ["**/node_modules/**", "**/.git/**", "**/vendor/**"],
    },
};

const cssConfig: CSSOptions = {
    modules: {
        generateScopedName: "[hash:base64:5]",
    },
    devSourcemap: true, // Enable CSS sourcemaps in development
};

const resolveConfig = {
    extensions: [".ts", ".tsx", ".js", ".json", ".vue"], // Add .vue for completeness
    alias: {
        "@": path.resolve(__dirname, "resources/js"),
        "ziggy-js": path.resolve("vendor/tightenco/ziggy"),
    },
};

// --- Main Vite Configuration ---

export default {
    build: buildConfig,
    server: serverConfig,
    css: cssConfig,
    plugins: [
        laravel({
            input: ["resources/js/app.ts"],
            ssr: "resources/js/ssr.ts",
        }),
        vue({
            ...templateCompilerOptions, // TresJS specific compiler options
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
                // make useful for global styles later
                // preprocessorOptions: {
                //   scss: {
                //     additionalData: `@import "@/styles/variables.scss";`
                //   }
                // }
            },
        }),
        basicSsl(), // For HTTPS in development

        // 2. Dev-only plugins (Conditional loading for production)
        process.env?.["APP_ENV"] === "local" && vueDevTools(), // Only load in development

        // Enable PWA in dev for testing
        process.env?.["APP_ENV"] === "local" &&
            VitePWA({
                registerType: "autoUpdate",
                devOptions: {
                    enabled: true,
                },
                // workbox: {
                //     globPatterns: ['**/*.{js,css,html,ico,png,svg,vue,ts,json}'],
                // },
                // manifest: { /* your PWA manifest */ },
                // display: 'standalone',
            }),

        // 4. Watch for specific tasks (use when ziggy stops acting like a bitch)
        /*
         watch({
         pattern: "routes/*.php",
         command: "php artisan ziggy:generate",
         }),
        */

        process.env?.["APP_ENV"] === "production" &&
            compression({
                algorithms: [
                    "gzip",
                    "brotliCompress",
                    defineAlgorithm("deflate", { level: 9 }),
                ],
                threshold: 1024, // Only compress files larger than 1kb
            }),
    ].filter(Boolean),

    resolve: resolveConfig,

    optimizeDeps: {
        include: [
            "ziggy-js",
            "vue",
            "@vue/runtime-core",
            "@vue/shared",
            "@tresjs/core",
        ],
    },
} satisfies UserConfig;
