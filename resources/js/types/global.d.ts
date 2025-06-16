import { PageProps as InertiaPageProps } from "@inertiajs/core";
import { AxiosInstance } from "axios";
import { Config as ZiggyConfig } from "ziggy-js";
import { route as routeFn } from "ziggy-js";
import Echo from "laravel-echo";
import Pusher from "pusher-js";

import { PageProps as AppPageProps } from "./";

declare global {
    interface Window {
        axios: AxiosInstance;
        Pusher: typeof Pusher;
        Echo: Echo;
    }

    var route: typeof routeFn;
    var Ziggy: ZiggyConfig;
}

declare module "vue" {
    interface ComponentCustomProperties {
        route: typeof routeFn;
        $device: Device;
        $breakpoints: {
            isMobile: boolean;
            isTablet: boolean;
            isDesktop: boolean;
            isPortable: boolean;
            current: string;
        };
    }
}

declare module "@inertiajs/core" {
    interface PageProps extends InertiaPageProps, AppPageProps {}
}
