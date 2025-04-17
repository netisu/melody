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

export default {
    server: {
        host: 'localhost',
        cors: true,
        hmr: {
            host: 'localhost',
        },
        watch: {
            usePolling: false,
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
        VitePWA({
            registerType: "autoUpdate",
            devOptions: {
                enabled: true,
            },
        }),
        watch({
            pattern: "routes/*.php",
            command: "php artisan trail:generate",
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
        },
    },
} satisfies UserConfig;
