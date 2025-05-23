<script setup lang="ts">
import { ref, onMounted, computed, watch } from "vue";
import { Link, usePage } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import type { Ref } from "vue";
import axios from 'axios';

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
        await axios.get(googleAdUrl, {
            timeout: 1000, // Update: Set a timeout for the request
        });
        adblock.value = false; // Request succeeded, so no adblock
    } catch (error) {
        adblock.value = true; // Request failed (due to adblocker)
        console.error('Error checking for adblock:', error.message);
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
    <div v-show="image" class="profile-banner" :class="{ special: OfficialImageBackground }">
        <img class="masoqi" :style="{
            backgroundImage: `url(${image})`,
            backgroundRepeat: OfficialImageBackground ? 'repeat' : 'no-repeat',
            backgroundSize: OfficialImageBackground ? '' : 'cover',
            backgroundPosition: 'center'
        }" />
    </div>
    <FlashMessages :flash="JSONALERT" />
    <slot name="SuperBanner" v-if="superBanActive" />
    <main class="container">
        <div v-show="alertsEnabled" style="
    z-index: 2;
    position: relative;
">
            <div v-show="adblock" class="py-2 mb-4 text-center alert alert-danger fw-semibold">
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
            <div v-show="props.site_config.in_maintenance" class="py-2 mb-4 text-center text-white alert alert-maintenance">
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
