<script setup lang="ts">
import { route } from 'ziggy-js';

// Props
defineProps<{
    link?: string,
    active_link?: any,
}>();
</script>
<template>
            <Link :href="link" :class="[route().current(active_link) ? 'has-text-link' : 'has-text-white']" class="navbar-item" prefetch>
                <slot />
            </Link>

</template>
