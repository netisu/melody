<script setup lang="ts">
import Navbar from '@/Components/LayoutParts/Navbar.vue';
import Sidebar from '@/Components/LayoutParts/Sidebar.vue';
import Footer from '@/Components/LayoutParts/Footer.vue';
import { route } from "ziggy-js";

defineProps<{
    status: string
    adonis_error: string
    message: string
    icon: string
}>();
</script>
<style scoped>
.fa-gradient.text-info {
    background: -webkit-gradient(linear, left top, left bottom, from(var(--info-600)), to(#fff));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.fa-gradient.text-danger {
    background: -webkit-gradient(linear, left top, left bottom, from(var(--danger-600)), to(#fff));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.fa-gradient.text-warning {
    background: -webkit-gradient(linear, left top, left bottom, from(var(--warning-600)), to(#fff));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}
</style>
<template>
<AppHead :pageTitle="status" :description="message" :url="route('welcome.page')" />
    <Navbar :landing="false" />
    <Sidebar>
        <div class="cell medium-7">
            <div class="text-center card">
                <div class="card-body">
                    <i class="mb-3" :class="icon" style="font-size:80px;"></i>
                    <h3>{{ status }}</h3>
                    <p>{{ message }}</p>
                </div>
            </div>
            <p class="mt-2 text-muted">
                <i>
                    {{ adonis_error }}
                </i>
            </p>
        </div>
    </Sidebar>
    <Footer />
</template>
