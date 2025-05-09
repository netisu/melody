<script setup lang="ts">
import { route } from 'ziggy-js';;

// Props
defineProps<{
    link?: string,
    ActiveLink?: any,
}>();
</script>
<template>
    <li class="side-item">
        <Link class="side-link" :href="link" :class="[route().current(ActiveLink) ? 'active' : '']" prefetch>
            <slot />
        </Link>
    </li>
</template>
