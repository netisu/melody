<script setup lang="ts">
import { usePage } from "@inertiajs/vue3";
import { computed } from "vue";
import { route } from 'ziggy-js';

const topbar = [
    {
        url: route(`admin.page`),
        active_link: "admin.page",
        icon: "fad fa-home",
        en: { title: "Dashboard" },
        es: { title: "Dashboard" },
        ru: { title: "Панель приборов" },
        ja: { title: "ダッシュボード" },
    },
    {
        url: route(`admin.users.page`),
        active_link: "admin.users.page",
        icon: "fad fa-user",
        en: { title: "User Search" },
        es: { title: "búsqueda de usuarios" },
        ru: { title: "Поиск пользователей" },
        ja: { title: "ユーザー検索" },
    },
    {
        url: route(`admin.items.page`),
        active_link: "admin.items.*",
        icon: "fad fa-tshirt",
        en: { title: "Item Search" },
        es: { title: "Búsqueda de artículos" },
        ru: { title: "Поиск предметов" },
        ja: { title: "アイテム検索" },
    },
];
const lang = computed<any>(() => props.locale);

const { props } = usePage<any>();
</script>
<template>
    <div class="mb-2 text-xl fw-semibold">{{ props.site.name }} Administration</div>
            <ul class="tabs flex-dir-column">
                <li class="tab-item">
                    <Link class="tab-link squish" v-for="topbarlinks in topbar" :href="topbarlinks.url"
                    :class="[route().current(topbarlinks.active_link) ? 'active' : '']">
                    <i :class="topbarlinks.icon"></i> &nbsp;
                    {{ topbarlinks[lang].title }}
                </Link>
                
                </li>
            </ul>
</template>
