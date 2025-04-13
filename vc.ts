import type { UserConfig } from 'vite'
import laravel from "laravel-vite-plugin";
import vue from "@vitejs/plugin-vue";
import path from "path";
import { watch } from "vite-plugin-watch";
import { obfuscator } from "rollup-obfuscator";
import basicSsl from '@vitejs/plugin-basic-ssl'
import { viteObfuscateFile } from "vite-plugin-obfuscator";
import { VitePWA } from "vite-plugin-pwa";
import { templateCompilerOptions } from '@tresjs/core'
import compression from 'vite-plugin-compression2'
// import autoprefixer from "autoprefixer";
// import postcssNested from "postcss-nested";
// import postcssObfuscator from "postcss-obfuscator";

export default {
    root: "", // Specify the root directory for development
    base: "/",
    build: {
        sourcemap: true,
    },
    optimizeDeps: {
        exclude: ["brotli-wasm", "brotli-wasm/pkg.bundler/brotli_wasm_bg.wasm"],
    },
    server: {
        host: '100.115.92.195',
        cors: true,
        hmr: {
            host: '100.115.92.195',
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
                //autoprefixer(),
                // postcssNested(),
                // postcssObfuscator({
                //        enable: true, // Force Run on Dev Env or when srcPath equals desPath.
                //        ids: true, //  Obfuscate #IdNames.
                //        jsonsPath: 'css-obfuscator',
                //        srcPath: "resources", // Source of your files.
                //        desPath: "public", // Destination for obfuscated html/js/.. files.
                //        extensions: ['.html', '.css', '.php', '.ts', '.vue'],
                //        formatJson: true, // Format obfuscation data JSON file.
                //        showConfig: true,
                //        fresh: true,
                //        keepData: true,
                //        multi: true,
                // }),
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
        // compression(),
        obfuscator({
            compact: true,
            controlFlowFlattening: false,
            controlFlowFlatteningThreshold: 0.75,
            deadCodeInjection: true,
            deadCodeInjectionThreshold: 0.4,
            debugProtection: false,
            debugProtectionInterval: 0,
            disableConsoleOutput: false,
            domainLock: [
                "100.115.92.195",
                "localhost:8000",
                "localhost",
                "netisu.com",
                "admin.netisu.com",
                "penguin.linux.test",
                "admin.penguin.linux.test",
            ],
            domainLockRedirectUrl: "about:blank",
            forceTransformStrings: [],
            identifierNamesCache: null,
            identifierNamesGenerator: "hexadecimal",
            identifiersDictionary: [],
            identifiersPrefix: "",
            ignoreImports: false,
            inputFileName: "",
            log: false,
            numbersToExpressions: false,
            optionsPreset: "default",
            renameGlobals: false,
            renameProperties: false,
            renamePropertiesMode: "safe",
            reservedNames: [],
            reservedStrings: [],
            seed: 0,
            selfDefending: false,
            simplify: true,
            sourceMap: false,
            sourceMapBaseUrl: "",
            sourceMapFileName: "",
            sourceMapMode: "separate",
            sourceMapSourcesMode: "sources-content",
            splitStrings: false,
            splitStringsChunkLength: 10,
            stringArray: true,
            stringArrayCallsTransform: true,
            stringArrayCallsTransformThreshold: 0.5,
            stringArrayEncoding: [],
            stringArrayIndexesType: ["hexadecimal-number"],
            stringArrayIndexShift: true,
            stringArrayRotate: true,
            stringArrayShuffle: true,
            stringArrayWrappersCount: 1,
            stringArrayWrappersChainedCalls: true,
            stringArrayWrappersParametersMaxCount: 2,
            stringArrayWrappersType: "variable",
            stringArrayThreshold: 0.75,
            target: "browser",
            transformObjectKeys: false,
            unicodeEscapeSequence: false,
        }),
        watch({
            pattern: "routes/*.php",
            command: "php artisan trail:generate",
        }),vue({
            ...templateCompilerOptions,
            template: {
                transformAssetUrls: {
                    // The Vue plugin will re-write asset URLs, when referenced
                    // in Single File Components, to point to the Laravel web
                    // server. Setting this to `null` allows the Laravel plugin
                    // to instead re-write asset URLs to point to the Vite
                    // server instead.
                    base: null,

                    // The Vue plugin will parse absolute URLs and treat them
                    // as absolute paths to files on disk. Setting this to
                    // `false` will leave absolute URLs un-touched so they can
                    // reference assets in the public directory as expected.
                    includeAbsolute: false,
                },
            },
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
        
        viteObfuscateFile({}),
        basicSsl(),
    ],
    resolve: {
        extensions: [".ts", ".tsx", ".js", ".json"],
        alias: {
            "@": path.resolve(__dirname, "resources/js"),
        },
    },
} satisfies UserConfig;
