// フレームワーク
import "./bootstrap";
import "vue-skeletor/dist/vue-skeletor.css";
import "../css/NProgress.css";
import "../css/style.css";

// アプリの作成
import { Skeletor } from "vue-skeletor";
import { createSSRApp, h } from "vue";
import type { DefineComponent } from "vue";
import { createInertiaApp, Head, Link } from "@inertiajs/vue3";
import Pagination from "./Components/Pagination.vue";
import AppHead from "./Components/AppHead.vue";
import { ZiggyVue } from 'ziggy-js';

if (import.meta.hot) {
    import.meta.hot.accept();
}

// エクストラ

import VueTippy from "vue-tippy";
import "tippy.js/dist/tippy-bundle.umd.min.js";
import "@popperjs/core/dist/umd/popper.min.js";
import "tippy.js/dist/tippy.css";
import "tippy.js/themes/light.css";


import { resolvePageComponent } from "laravel-vite-plugin/inertia-helpers";

const appName = import.meta.env?.["VITE_APP_NAME"] || "Laravel";

createInertiaApp({
    progress: { includeCSS: true, showSpinner: false },
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob<DefineComponent>('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        createSSRApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue) 
            .use(VueTippy)
            .component("Skeletor", Skeletor)
            .component("Pagination", Pagination)
            .component("AppHead", AppHead)
            .component("Head", Head)
            .component("Link", Link)
            .mount(el);
    },
});