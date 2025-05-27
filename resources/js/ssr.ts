// フレームワーク
// import "./bootstrap";
import "vue-skeletor/dist/vue-skeletor.css";
import "../css/NProgress.css";
import "../css/style.css";

// アプリの作成
import { createSSRApp, type DefineComponent, h } from "vue";
import { i18nVue } from "laravel-vue-i18n";
import { ZiggyVue } from 'ziggy-js';
import { Skeletor } from "vue-skeletor";
import AppHead from "./Components/AppHead.vue";
import createServer from "@inertiajs/vue3/server";
import { renderToString } from "@vue/server-renderer";
import Pagination from "./Components/Pagination.vue";
import { createInertiaApp, Head, Link } from "@inertiajs/vue3";
import "../css/style.css";

if (import.meta.hot) {
    import.meta.hot.accept();
}

// エクストラ
import VueTippy from "vue-tippy";
import "tippy.js/dist/tippy-bundle.umd.min.js";
import "@popperjs/core/dist/umd/popper.min.js";
import { resolvePageComponent } from "laravel-vite-plugin/inertia-helpers";

const appName = import.meta.env?.["VITE_APP_NAME"] || "Laravel";

createServer((page) =>
    createInertiaApp({
        page,
        render: renderToString,
        progress: { includeCSS: true, showSpinner: false },
        title: (title) => `${title} - ${appName}`,
        resolve: (name) =>
            resolvePageComponent(
                `./Pages/${name}.vue`,
                import.meta.glob<DefineComponent>("./Pages/**/*.vue"),
            ),
        setup({ App, props, plugin }) {
            return createSSRApp({ render: () => h(App, props) })
                .use(ZiggyVue)
                .use(plugin)
                .use(VueTippy)
                .component("Skeletor", Skeletor)
                .component("Pagination", Pagination)
                .component("AppHead", AppHead)
                .component("Head", Head)
                .component("Link", Link)
                .use(i18nVue, {
					lang: page.props.locale, /* use correct language server-side */
					resolve: lang => {
						const langs = import.meta.glob('../../lang/*.json', { eager: true });
						return langs[`../../lang/${lang}.json`].default;
					},
				});
        },
    }),
);
