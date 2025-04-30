<script setup lang="ts">
import { current } from 'momentum-trail';

// Props
defineProps<{
    link?: string,
    ActiveLink?: any,
    showForLarge?: boolean
}>();
</script>
<template>
    <li class="nav-item cell shrink show-for-large">
        <div class="side-item">
            <Link class="nav-link" :href="link" :class="[{ 'active': current(ActiveLink) }, { 'show-for-large': showForLarge }]" prefetch>
                <slot />
            </Link>
        </div>
    </li>
</template>
