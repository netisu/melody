<script setup lang="ts">
import { current } from 'momentum-trail';

// Props
defineProps<{
    link?: string,
    active_link?: any,
}>();
</script>
<template>
            <Link :href="link" :class="[current(active_link) ? 'has-text-link' : 'has-text-white']" class="navbar-item" prefetch>
                <slot />
            </Link>

</template>
