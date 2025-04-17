<script setup lang="ts">
import { current } from "momentum-trail";

// Props
defineProps<{
    link?: string,
    ActiveLink?: any,
}>();
</script>
<template>
    <li class="side-item">
        <Link class="side-link" :href="link" :class="[current(ActiveLink) ? 'active' : '']" prefetch>
            <slot />
        </Link>
    </li>
</template>
