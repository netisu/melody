<script setup lang="ts">
import { route } from 'ziggy-js';

// Props
defineProps<{
    link?: string,
    ActiveLink?: any,
    showForLarge?: boolean,
}>();
</script>
<template>
    <li class="nav-item cell shrink" :class="{ 'show-for-large': showForLarge }">
        <div class="side-item">
            <Link class="nav-link" :href="link" :class="{'active': route().current(ActiveLink) }" prefetch>
                <slot />
            </Link>
        </div>
    </li>
</template>
