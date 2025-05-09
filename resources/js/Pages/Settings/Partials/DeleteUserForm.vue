<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { nextTick, ref } from 'vue';
import { route } from 'ziggy-js';


const confirmingUserDeletion = ref(false);
const passwordInput = ref(null);

const form = useForm({
    password: '',
});

const confirmUserDeletion = () => {
    confirmingUserDeletion.value = true;

    nextTick(() => passwordInput.value.focus());
};

const deleteUser = () => {
    form.post(route('user.settings.destroy'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value.focus(),
        onFinish: () => form.reset(),
    });
};

const closeModal = () => {
    confirmingUserDeletion.value = false;

    form.reset();
};
</script>

<template>
    <button class="btn btn-danger btn-circle" @click="confirmUserDeletion">
        <i class="fas fa-trash"></i>
    </button>

    <div v-bind:class="{ 'active': confirmingUserDeletion }" class="modal" @close="closeModal" id="DeleteAcceountModal">
        <form @submit.prevent="submit">
            <div class="modal-card modal-card-body modal-card-sm">
                <div class="section-borderless">
                    <div class="gap-2 align-middle flex-container align-justify">
                        <div class="text-lg text-danger fw-semibold">Delete Account</div>
                        <button class="btn-circle" @click="closeModal" style="margin-right: -10px">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                <div class="section-borderless">
                    <div class="mb-2">
                        <p class="fw-semibold text-warning text-md mb-1">Are you sure you want to delete your account?
                        </p>
                        <div class="text-xs text-muted fw-semibold mb-2">
                            Please enter your password to confirm you would like to permanently delete your account.
                        </div>
                        <div class="text-xs fw-bold text-muted text-uppercase">
                            Password
                        </div>
                        <div class="field">
                            <div class="control">
                                <input id="password" ref="passwordInput" v-model="form.password" type="password"
                                    class="form" placeholder="Password" @keyup.enter="deleteUser" />
                            </div>
                        </div>
                        <div :message="form.errors.password" class="mt-2" />
                        <div class="min-w-0 gap-2 mt-3 flex-container">
                            <button class="btn btn-info btn-sm text-truncate w-100" @click="closeModal">Cancel</button>
                            <button class="btn btn-danger btn-sm text-truncate w-100" :disabled="form.processing"
                                @click="deleteUser">
                                Close Account
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</template>
