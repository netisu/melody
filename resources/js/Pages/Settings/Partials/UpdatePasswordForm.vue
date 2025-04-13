<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { route } from 'momentum-trail'
import axios from 'axios';
import { ref } from 'vue';

const passwordInput = ref(null);
const currentPasswordInput = ref(null);

const form = useForm({
    current_password: '',
    new_password: '',
    new_password_confirmation: '',
});

const updatePassword = () => {
    axios.get(`/sanctum/csrf-cookie`).then(() => {
        form.post(route('api.settings.changePassword'), {
            preserveScroll: true,
            onSuccess: () => form.reset(),
            onError: () => {
                if (form.errors.password) {
                    form.reset('new_password', 'new_password_confirmation');
                    passwordInput.value.focus();
                }
                if (form.errors.current_password) {
                    form.reset('current_password');
                    currentPasswordInput.value.focus();
                }
            },
        });
    })
};
</script>

<template>
    <div class="card card-inner card-body">
        <form @submit.prevent="updatePassword" class="mt-6 space-y-6">

            <div class="mb-2">
                <div class="mb-2 text-xl fw-semibold">
                    Change Password
                </div>
                <div class="mb-2">
                    <div class="text-xs fw-bold text-muted text-uppercase">
                        Current Password
                    </div>
                    <input id="current_password" ref="currentPasswordInput" v-model="form.current_password"
                        type="password" class="form form-has-section-color" autocomplete="current-password"
                        placeholder="Current Password..." />
                    <div :message="form.errors.current_password" class="text-danger fw-semibold mt-2" />
                </div>
                <div class="mb-2">
                    <div class="text-xs fw-bold text-muted text-uppercase">
                        New Password
                    </div>
                    <input id="password" ref="passwordInput" v-model="form.new_password" type="password"
                        autocomplete="new-password" class="form form-has-section-color"
                        placeholder="Current Password..." />
                    <div :message="form.errors.new_password" class="text-danger fw-semibold mt-2" />

                </div>
                <div class="mb-2">
                    <div class="text-xs fw-bold text-muted text-uppercase">
                        Confirm Password
                    </div>
                    <input id="password_confirmation" v-model="form.new_password_confirmation" type="password"
                        autocomplete="new-password" class="form form-has-section-color"
                        placeholder="Current Password..." />
                    <div :message="form.errors.new_password_confirmation" class="text-danger fw-semibold mt-2" />
                </div>
            </div>
            <button :disabled="form.processing" class="btn btn-success">
                Change Password
            </button>
        </form>
    </div>
</template>
