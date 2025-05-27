<script setup lang="ts">
import VLazyImage from "v-lazy-image";

// Props
defineProps<{
    link?: String;
    name?: String;
    image?: String;
    icon?: String;
}>();
</script>

<template>
    <li class="dropdown-item">
        <Link :href="link" class="dropdown-link" prefetch>
            <div class="align-middle flex-container align-justify">
                <div class="gap-2 align-middle flex-container">
                    <VLazyImage
                        v-if="image"
                        class="headshot flex-child-grow"
                        alt="headshot"
                        width="40"
                        :src="image"
                        src-placeholder="/assets/img/dummy_headshot.png"
                    />
                    <i
                        v-else-if="icon"
                        class="text-2xl text-body"
                        :class="icon"
                    ></i>
                    <VLazyImage
                        class="headshot flex-child-grow"
                        alt="headshot"
                        width="40"
                        :src="image"
                        src-placeholder="/assets/img/dummy_headshot.png"
                    />
                    <div>{{ name }}</div>
                </div>
                <i class="px-1 fas fa-chevron-right text-muted"></i>
            </div>
        </Link>
    </li>
</template>
