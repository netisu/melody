import type { UserConfig } from 'vite'
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import autoprefixer from "autoprefixer";
import postcssNested from "postcss-nested";
import { watch } from "vite-plugin-watch";
import { VitePWA } from "vite-plugin-pwa";
import { templateCompilerOptions } from '@tresjs/core'
import path from "path";
import basicSsl from '@vitejs/plugin-basic-ssl'
import viteImagemin from 'vite-plugin-imagemin'
import compression from 'vite-plugin-compression2'
import vueDevTools from 'vite-plugin-vue-devtools'

export default {
    server: {
        host: '100.115.92.197',
        cors: true,
        strictPort: true,
        hmr: {
            host: '100.115.92.197',
        },
        watch: {
            usePolling: true,
        },
    },
    css: {
        modules: {
            generateScopedName: "[hash:base64:5]",
        },
        postcss: {
            plugins: [
                 autoprefixer(),
                 postcssNested(),
            ],
        },

    },
    plugins: [
        vueDevTools(),
        VitePWA({
            registerType: "autoUpdate",
            devOptions: {
                enabled: true,
            },
        }),
        watch({
            pattern: "routes/*.php",
            command: "php artisan ziggy:generate --types",
        }),
        laravel({
            input: ["resources/js/app.ts"],
            ssr: "resources/js/ssr.ts",
            /*
            refresh: [
              "resources/routes/**",
              "app/Http/**",
              "public/assets/js/**",
              "public/assets/img/**",
              "routes/**",
            ],*/
        }),
        vue({
            ...templateCompilerOptions,
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        basicSsl(),
        viteImagemin({}),
        compression()
    ],
    resolve: {
        extensions: [".ts", ".tsx", ".js", ".json"],
        alias: {
            "@": path.resolve(__dirname, "resources/js"),
            'ziggy-js': path.resolve('vendor/tightenco/ziggy'),
        },
    },
    optimizeDeps: {
        include: ["ziggy-js"],
    },
} satisfies UserConfig;
