<script setup lang="ts">
import { usePage } from "@inertiajs/vue3";
</script>

<template>
        <main :class="{
            'container-alert container': usePage<any>().props.site_config.announcement,
            'container-navbar': !usePage<any>().props.site.frontend.sidebar_menu,
            'container': usePage<any>().props.site.frontend.sidebar_menu && usePage<any>().props.site_config.announcement,
        }">
            <div class="gap-4 align-middle flex-container" style="z-index: 1; position: relative">
                <slot name="bannerAsset" />
                <div class="text-start">
                    <div class="text-2xl fw-semibold">
                        <slot name="bannerName" />
                    </div>
                    <div class="text-sm text-truncate fw-semibold text-muted">
                        <slot name="bannerDescription" />
                    </div>
                    <div class="gap-2 align-middle flex-container">
                        <slot name="bannerButtons" />
                    </div>
                </div>
            </div>
        </main>
</template>
