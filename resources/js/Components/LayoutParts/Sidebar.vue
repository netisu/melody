<script setup lang="ts">
import { ref, onMounted, computed, watch } from "vue";
import { Link, usePage } from "@inertiajs/vue3";
import { route } from 'momentum-trail';

import SideLink from "../SideLink.vue";
import FlashMessages from "@/Components/Messages/FlashMessages.vue";

import VLazyImage from "v-lazy-image";



interface Props {
    image?: string;
    JSONALERT?: { message: string; type: string } | null;
    superBanActive?: boolean;
    alertsEnabled?: boolean;
    OfficialImageBackground?: boolean;
    navSpaces?: Array<any>;
}

withDefaults(defineProps<Props>(), {
    alertsEnabled: true,
    superBanActive: false,
    OfficialImageBackground: false,
});


const chatToggle = ref(false);
const { props } = usePage<any>();
const googleAdUrl = "https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js";
const adblock = ref(false);
const messages = ref([
    {
        username: "aeo",
        displayname: "ae-no",
        message: "test array",
        type: "type",
        DateHum: "Just Now",
    },
]);
async function detectAdBlock(adblock: Ref<boolean, boolean>) {
    try {
        await fetch(new Request(googleAdUrl)).catch(
            adblock.value = false
        );
    } catch (e) {
        adblock.value = true;
    }
}

onMounted(() => {
    detectAdBlock(adblock); // Call the function only if adblock status is not already set
});

const sidebarsections = [
    {
        en: { name: "NAVIGATION" },
        es: { name: "NAVEGACIÓN" },
        ru: { name: "НАВИГАЦИЯ" },
        ja: { name: "ナビゲーション" },
    },
    {
        en: { name: "SOCIAL" },
        es: { name: "Sociable" },
        ru: { name: "СОЦИАЛЬНОЕ" },
        ja: { name: "ソーシャル" },
    },
    {
        en: { name: "BOOST YOUR ACCOUNT" },
        es: { name: "IMPULSA TU CUENTA" },
        ru: { name: "ПОДДЕРЖИТЕ СВОЙ АККАУНТ" },
        ja: { name: "アカウントを強化する" },
    },
];
const sidebar = [
    {
        url: route(`games.page`),
        ActiveLink: "games.*",
        section: "NAVIGATION",
        icon: "fad fa-gamepad-modern",
        en: { title: "Games" },
        es: { title: "Juegos" },
        ru: { title: "Игры" },
        ja: { title: "ゲーム" },
    },
    {
        url: route(`store.page`),
        section: "NAVIGATION",
        ActiveLink: "store.*",
        icon: "fad fa-store",
        en: { title: "Market" },
        es: { title: "Mercado" },
        ru: { title: "Маркет" },
        ja: { title: "市場" },
    },
    {
        url: route(`forum.page`, { id: 1 }),
        ActiveLink: "forum.*",
        icon: "fad fa-messages",
        section: "NAVIGATION",
        en: { title: "Discuss" },
        es: { title: "Conversar" },
        ru: { title: "Обсуждение" },
        ja: { title: "議論" },
    },
    {
        url: "#",
        section: "NAVIGATION",
        ActiveLink: "develop.*",
        icon: "fad fa-code",
        en: { title: "Develop" },
        es: { title: "Desarrollar" },
        ru: { title: "Мои создания" },
        ja: { title: "発展" },
    },
    {
        url: route(`user.page`),
        ActiveLink: "user.*",
        section: "SOCIAL",
        icon: "fad fa-users",
        en: { title: "Players" },
        es: { title: "Jugadoras" },
        ru: { title: "Игроки" },
        ja: { title: "ユーザー" },
    },
    {
        url: route(`spaces.page`),
        section: "SOCIAL",
        ActiveLink: "spaces.*",
        icon: "fad fa-planet-ringed",
        en: { title: "Spaces" },
        es: { title: "Espacios" },
        ru: { title: "Пространства" },
        ja: { title: "スペース" },
    },
    {
        url: route(`leaderboard.page`),
        section: "SOCIAL",
        ActiveLink: "leaderboard.page",
        icon: "fad fa-list-ol",
        en: { title: "Leaderboard" },
        es: { title: "Tabla de clasificación" },
        ru: { title: "Таблица лидеров" },
        ja: { title: "リーダーボード" },
    }, {
        url: route(`promocodes.page`),
        section: "SOCIAL",
        ActiveLink: "promocodes.page",
        icon: "fad fa-ticket",
        en: { title: "Promocodes" },
        es: { title: "Códigos promocionales" },
        ru: { title: "Промокоды" },
        ja: { title: "プロモコード" },
    },
    {
        url: "#",
        icon: "fad fa-rocket-launch",
        ActiveLink: "upgade.*",
        section: "BOOST YOUR ACCOUNT",
        en: { title: "Upgrade" },
        es: { title: "Mejora" },
        ru: { title: "Подписки" },
        ja: { title: "アップグレード" },
    },
];
const lang = computed(() => usePage<any>().props.locale);
const localJSONALERT = ref(props.JSONALERT);

watch(
    () => props.JSONALERT,
    (newVal) => {
        localJSONALERT.value = newVal;
    }
);
</script>
<template>
    <nav v-if="props.site.frontend.sidebar_menu" class="sidebar show-for-large" id="sidebar">
        <ul class="sidebar-nav">
            <div class="hide-for-large" v-if="!props.auth.user">
                <li class="side-item side-title">Account</li>
                <SideLink :link="route('auth.register.page')" :ActiveLink="route('auth.register.page')">
                    <i class="fad fa-user-plus side-icon"></i>
                    <span>Get Started</span>
                </SideLink>
                <SideLink :link="route('auth.login.page')" :ActiveLink="route('auth.login.page')">
                    <i class="fad fa-sign-in side-icon"></i>
                    <span>Log In</span>
                </SideLink>
            </div>
            <SideLink :link="route('Welcome')" :ActiveLink="props.auth.user ? 'my.dashboard.page' : 'Welcome'">
                <i class="fad fa-home side-icon"></i>
                <span> {{ props.auth.user ? "Dashboard" : "Welcome" }} </span>
            </SideLink>
            <template v-for="sidesections in sidebarsections" class="hide-for-large">
                <li class="side-item side-title">
                    {{ sidesections[lang].name }}
                </li>
                <!-- Iterate over links within the current section -->
                <SideLink class="side-item" v-for="sidelink in sidebar.filter(
                    (link) => link.section === sidesections.en.name
                )" :link="sidelink.url" :ActiveLink="sidelink.ActiveLink">
                    <i class="side-icon" :class="sidelink.icon"></i><span>{{ sidelink[lang].title }}</span>
                </SideLink>
            </template>
            <div v-if="usePage<any>().props.auth.user">
                <div v-if="usePage<any>().props.auth.user.mainSpaces">
                <li class="side-item side-title">
                    Main Spaces
                </li>
                <li v-if="usePage<any>().props.auth.user.mainSpaces"
                    v-for="(space, index) in usePage<any>().props.auth.user.mainSpaces" :key="index" class="side-item">
                    <Link :href="route(`spaces.view`, { id: space.id, slug: space.slug })"
                        class="side-link side-link-has-img">
                    <span class="side-img">
                        <v-lazy-image class="headshot" :src="space.thumbnail" />
                    </span>
                    <span>{{ space.name }}</span>
                    </Link>
                </li>
                </div>
                <li class="side-item side-title">
                    Joined Spaces
                </li>
                <li v-if="usePage<any>().props.auth.user.navSpaces.length"
                    v-for="(space, index) in usePage<any>().props.auth.user.navSpaces" :key="index" class="side-item">
                    <Link :href="route(`spaces.view`, { id: space.id, slug: space.slug })"
                        class="side-link side-link-has-img">
                    <span class="side-img">
                        <v-lazy-image class="headshot" :src="space.thumbnail" />
                    </span>
                    <span>{{ space.name }}</span>
                    </Link>
                </li>
                <li v-else class="side-item">
                    <Link :href="route('spaces.page')" class="side-link side-link-has-img">
                    <button class="gap-2 align-middle flex-container squish" style="outline: none;">
                        <span class="side-img">
                            <i class="fad fa-square-xmark" />
                        </span>

                        <div class="text-start">
                            <div class="text-sm fw-semibold text-body">No spaces found</div>
                            <div class="mb-1 text-xs text-muted fw-semibold"> Join a space here </div>
                        </div>
                    </button>
                    </Link>
                </li>
            </div>
        </ul>
    </nav>
    <nav v-if="props.site_config.announcement" class="sitewide-alert">
        <div class="py-2 text-center alert alert-danger alert-wide fw-semibold">
            <div class="gap-2 text-center align-middle flex-container align-center">
                <div>
                    {{ props.site_config.announcement_message }}
                </div>
            </div>
        </div>
    </nav>
    <div v-if="image" class="profile-banner" :class="{ special: OfficialImageBackground }">
        <img class="masoqi" :style="{
            backgroundImage: `url(${image})`,
            backgroundRepeat: OfficialImageBackground ? 'repeat' : 'no-repeat',
            backgroundSize: OfficialImageBackground ? '' : 'cover',
            backgroundPosition: 'center'
        }" />
    </div>
    <FlashMessages :flash="JSONALERT" />
    <slot name="SuperBanner" v-if="superBanActive" />
    <main :class="{
        'container-alert': props.site_config.announcement,
        'container-navbar': !props.site.frontend.sidebar_menu,
        'container': props.site.frontend.sidebar_menu && props.site_config.announcement,
    }">
        <div v-if="alertsEnabled">
            <div v-if="adblock" class="py-2 mb-4 text-center alert alert-danger fw-semibold">
                <div class="gap-2 align-middle flex-container align-justify">
                    <i class="text-lg far fa-triangle-exclamation pe-2"></i>
                    <div>
                        Looks like you're using an adblocker! Please consider
                        disabling your adblocker to support
                        {{ props.site.name }}.
                    </div>
                    <i class="text-lg far fa-triangle-exclamation pe-2"></i>
                </div>
            </div>
            <div v-if="props.site_config.in_maintenance" class="py-2 mb-4 text-center text-white alert alert-maintenance">
                <div class="gap-2 align-middle flex-container align-justify">
                    <i class="text-lg fad fa-hammer pe-2"></i>
                    <div>
                        You are currently in maintenance mode.
                        <Link :href="route('maintenance.exit')" class="ml-2 text-white fw-semibold">Exit</Link>
                    </div>
                    <i class="text-lg fad fa-hammer pe-2"></i>
                </div>
            </div>
            <Link as="div" style="cursor:pointer;" v-if="props.auth.user && props.auth.user.email_verified_at === null"
                :href="route('verification.notice')" class="py-2 mb-4 text-center text-white alert alert-danger">
            <div class="gap-2 align-middle flex-container align-justify">
                <i class="text-lg fad fa-envelope pe-2"></i>
                <div>
                    <span v-if="props.auth.user.settings.verified_email">
                        Please check your email at <span class="fw-semibold">{{ props.auth.user.email }}</span>.
                        If you dont see the email, check your spam folder.
                    </span>
                    <span v-else class="d-block">
                        We need to verify your email at
                        <span class="fw-semibold">{{ props.auth.user.email }}</span>
                        to keep your account in good standing.
                    </span>
                </div>
                <i class="text-lg fad fa-envelope pe-2"></i>
            </div>
            </Link>
        </div>
        <div class="grid-x grid-margin-x align-center">
            <slot />
            <div id="chat-container" class="chat-container chat-vis focused" style="right: 66px; z-index: 1060"
                v-if="chatToggle">
                <div class="chat-windows-header chat-header bg-info hover" @click="chatToggle = !chatToggle">
                    <div class="chat-header-action">
                        <i class="chat-icon fad fa-message-xmark chat-link-icon"></i>
                        <i class="chat-icon fad fa-cog chat-link-icon"></i>
                        <i class="chat-icon fad fa-gamepad-modern chat-link-icon"></i>
                    </div>
                    <div class="chat-header-label">
                        <span class="chat-caption-header text-overflow chat-header-title">
                            Aeo
                        </span>
                    </div>
                </div>
                <div class="chat-main">
                    <div class="chat-body card-chat card-chat-body no-corners">
                        <div v-for="message in messages"
                            class="gap-2 p-2 mb-2 align-middle squish flex-container align-justify">
                            <div class="gap-2 align-middle flex-container">
                                <img src="/assets/img/dummy_headshot.png" class="headshot" width="50" />
                                <div class="w-100">
                                    <div class="fw-semibold">
                                        {{ message.displayname }}
                                    </div>
                                    <div class="text-sm fw-semibold text-muted">
                                        @{{ message.username }}
                                    </div>
                                    <div class="text-xs fw-semibold text-muted">
                                        {{ message.DateHum }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <!-- End #main -->
</template>
<style scoped>
.msg {
    border-radius: 10px;
}

.msg.right {
    background-color: var(--info-600);
}

.msg.left {
    background-color: var(--gray-500);
    color: white;
}
</style>
<script lang="ts">
export default {
    methods: {
        showModal(modalId: string): void {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.toggle("active");
            }
        },
        addActiveClass(elementId: string): void {
            const element = document.getElementById(elementId);
            if (element) {
                element.classList.toggle("active");
            }
        },
        addActiveClassSSInput(elementId: string): void {
            const element = document.getElementById(
                elementId
            ) as HTMLInputElement;
            const isEmpty = (str: string): boolean => !str.trim().length;

            if (element) {
                element.addEventListener("input", function () {
                    if (isEmpty(this.value)) {
                        return;
                    } else {
                        element.classList.toggle("hide");
                    }
                });
            }
        },
    },
};
</script>
