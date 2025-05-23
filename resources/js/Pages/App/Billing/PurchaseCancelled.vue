<script setup lang="ts">
import Navbar from '@/Components/LayoutParts/Navbar.vue';
import Sidebar from '@/Components/LayoutParts/Sidebar.vue';
import Footer from '@/Components/LayoutParts/Footer.vue';
import { route } from "ziggy-js";
</script>

<template>
    <AppHead pageTitle="Purchase Cancelled" description="You have not been charged." :url="route('Welcome')" />
    <Navbar :landing="false" />
    <Sidebar>
        <div class="cell medium-7">
            <div class="text-center card">
                <div class="card-body">
                    <i class="mb-3 fad fa-x text-info" style="font-size:80px;"></i>
                    <h3>Your purchase has been cancelled.</h3>
                    <p class="text-muted text-sm">You have not been charged.</p>
                </div>
            </div>
        </div>
    </Sidebar>
    <Footer />
</template>
