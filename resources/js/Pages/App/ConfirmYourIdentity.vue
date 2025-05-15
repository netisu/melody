<template>
    <AppHead pageTitle="Confirm Your Identity" description="Before you can access this page, you must
                            verify your identity by typing in your
                            account password below." :url="route('password.confirm.form')" />
    <Navbar />
    <Sidebar>
        <div class="cell medium-6">
            <div class="text-xl fw-semibold mb-1">
                Confirm Your Identity
            </div>
            <div class="card card-body">
                <div class="flex-container align-middle gap-4">
                    <i class="fad fa-lock text-6xl mx-1 text-muted"></i>
                    <div>
                        <div class="fw-semibold text-sm text-muted mb-2">
                            Before you can access this page, you must
                            verify your identity by typing in your
                            account password below.
                        </div>
                        <form @submit.prevent="submitPassword">

                        <div class="flex-container gap-2">
                                    <input type="password" class="form" placeholder="Password..."
                                        v-model="password" ref="passwordInput" />
                                    <button class="btn btn-success" :disabled="isSubmitting">
                                        <i :class="[ isSubmitting ? 'fa-loader fa-spin' : 'fa-right-to-bracket' ]" class="fa-duotone"></i>
                                    </button>
                        </div>
                    </form>
                    <div v-if="errorMessage" class="fw-semibold text-xs text-danger mt-2">
                            {{ errorMessage }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </Sidebar>
    <Footer />
</template>

<script lang="ts" setup>
import { ref, onMounted } from 'vue';
import { route } from "ziggy-js";
import { router, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import Navbar from "@/Components/LayoutParts/Navbar.vue";
import Sidebar from "@/Components/LayoutParts/Sidebar.vue";
import Footer from "@/Components/LayoutParts/Footer.vue"

const password = ref('');
const isSubmitting = ref(false);
const errorMessage = ref('');
const passwordInput = ref(null);
const page = usePage();

onMounted(() => {
    if (passwordInput.value) {
        passwordInput.value.focus();
    }
});

const submitPassword = async () => {
    if (!password.value) {
        errorMessage.value = 'Please enter your password.';
        return;
    }

    isSubmitting.value = true;
    errorMessage.value = '';

    try {
        const response = await axios.post(route('password.confirm'), {
            password: password.value,
            intended: page.props?.['intendedUrl'], // Pass the originally requested URL
        });

        if (response.data.intended) {
            router.visit(response.data.intended);
        } else {
            // Handle unexpected success response
            console.error('Password confirmed, but no intended URL received.');
            router.visit(route('my.dashboard.page'));
        }
    } catch (error) {
        isSubmitting.value = false;
        if (error.response && error.response.status === 422) {
            errorMessage.value = error.response.data.message || 'Invalid password.';
        } else {
            errorMessage.value = 'An error occurred during verification.';
            console.error('Password verification error:', error);
        }
    } finally {
        isSubmitting.value = false;
    }
};
</script>
