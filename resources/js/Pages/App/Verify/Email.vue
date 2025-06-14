<script setup lang="ts">
import Navbar from '@/Components/LayoutParts/Navbar.vue';
import Sidebar from '@/Components/LayoutParts/Sidebar.vue';
import Footer from '@/Components/LayoutParts/Footer.vue';
import { route } from "ziggy-js";
import axios from 'axios';
import { ref } from 'vue';

function getCsrfToken() {
    return axios.get(`/sanctum/csrf-cookie`);
}

function postEmail() {
    return axios.post(route(`verification.send`)).then(
        setTimeout(function () { window.location.reload() }, 2000)
    );
}


const sendEmail = () => {
    return Promise.all([getCsrfToken(), postEmail()]);
}
</script>

<template>
    <AppHead pageTitle="Email Verification" description="Please verify your email to continue."
        :url="route('verification.notice')" />
    <Navbar :landing="false" />
    <Sidebar>
        <div class="cell medium-7">
            <div class="text-center card">
                <div class="card-body">
                    <i class="mb-3 fad fa-envelope-open-text text-muted" style="font-size:80px;"></i>
                    <h3>Email Verification</h3>
                    <p class="text-muted text-sm">This will take less than 5 minutes.</p>
                    <form @submit.prevent="sendEmail">
                        <div class="min-w-0 gap-1 mt-3 flex-container align-center">
                            <button type="submit" class="btn btn-xs btn-success">
                                <i class="fad fa-badge-check"></i>
                                Verify
                            </button>
                            <button type="button" class="btn btn-xs btn-danger">
                                <i class="fad fa-right-from-bracket"></i>
                                Logout
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </Sidebar>
    <Footer />
</template>
