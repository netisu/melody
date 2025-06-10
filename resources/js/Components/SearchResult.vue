<script setup lang="ts">
import VLazyImage from "v-lazy-image";
import DummyHeadshot from "@/Images/dummy_headshot.png";

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
                        :src-placeholder="DummyHeadshot"
                    />
                    <i
                        v-else-if="icon"
                        :class="icon"
                        class="text-xl align-middle headshot text-muted flex-container align-center flex-child-grow"
                        style="height: 40px; width: 40px"
                    ></i>
                    <i
                        v-else
                        class="text-xl fa-duotone fa-seal-question align-middle headshot text-muted flex-container align-center flex-child-grow"
                        style="height: 40px; width: 40px"
                    ></i>
                    <div>{{ name }}</div>
                </div>
                <i class="px-1 fas fa-chevron-right text-muted"></i>
            </div>
        </Link>
    </li>
</template>
