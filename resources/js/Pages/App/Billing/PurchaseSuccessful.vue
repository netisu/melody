<script setup lang="ts">
import Navbar from '@/Components/LayoutParts/Navbar.vue';
import Sidebar from '@/Components/LayoutParts/Sidebar.vue';
import Footer from '@/Components/LayoutParts/Footer.vue';
import { usePage } from '@inertiajs/vue3';
import { route } from "ziggy-js";
</script>

<template>
    <AppHead pageTitle="Purchase Successful" description="You will receive your items within 5-10 minutes." :url="route('Welcome')" />
    <Navbar :landing="false" />
    <Sidebar>
        <div class="cell medium-7">
            <div class="text-center card">
                <div class="card-body">
                    <i class="mb-3 fad fa-check-circle text-info" style="font-size:80px;"></i>
                    <h3>Your purchase has been successful.</h3>
                    <p class="text-muted text-sm">You will receive your items within 5-10 minutes.</p>
                </div>
            </div>
        </div>
    </Sidebar>
    <Footer />
</template>
