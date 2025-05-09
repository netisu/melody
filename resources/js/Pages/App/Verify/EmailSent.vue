<script setup lang="ts">
import Navbar from '@/Components/LayoutParts/Navbar.vue';
import Sidebar from '@/Components/LayoutParts/Sidebar.vue';
import Footer from '@/Components/LayoutParts/Footer.vue';
import { route } from "ziggy-js";

</script>

<template>
    <AppHead pageTitle="Email Sent" description="Email Sent"
        :url="route('Welcome')" />
    <Navbar :landing="false" />
    <Sidebar>
        <div class="cell medium-7">
            <div class="text-center card">
                <div class="card-body">
                    <i class="mb-3 fad fa-check-circle text-info" style="font-size:80px;"></i>
                    <h3>Email Sent</h3>
                    <p class="text-muted text-sm">If you dont see the email, check your spam folder.</p>
                </div>
            </div>
        </div>
    </Sidebar>
    <Footer />
</template>
