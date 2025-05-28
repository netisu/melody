<script setup lang="ts">
import Navbar from "@/Components/LayoutParts/Navbar.vue";
import { useForm, usePage } from "@inertiajs/vue3";
import { ref, watch } from "vue";
import { route } from "ziggy-js";

import FlashMessages from "@/Components/Messages/FlashMessages.vue";
import AppHead from "@/Components/AppHead.vue";
import Sidebar from "@/Components/LayoutParts/Sidebar.vue";
import axios from "axios";
import Footer from "@/Components/LayoutParts/Footer.vue";
import VLazyImage from "v-lazy-image";

const ConfirmingAuth = ref(false);
const userImageUrl = ref("");
const isLoadingImage = ref(false);
const form = useForm({
    username: "",
    password: "",
    remember: false,
});

const submit = () => {
    axios.get(`/sanctum/csrf-cookie`).then(() => {
        form.post(route(`auth.login.validate`), {
            onFinish: () => form.reset("password"),
        });
    });
};

const ConfirmUserAuth = () => {
    ConfirmingAuth.value = true;
};

watch(
    () => form.username,
    async (username) => {
        // Only attempt to fetch if there's a username
        if (username.trim()) {
            isLoadingImage.value = true; // Indicate that we're loading the image
            try {
                const response = await axios.get(
                    route(`api.user.profile-image`, { username: username })
                );

                if (response.data && response.data) {
                    userImageUrl.value = response.data;
                } else {
                    userImageUrl.value = "/assets/img/dummy_headshot.png";
                }
            } catch (error) {
                console.error("Error fetching user image:", error);
                userImageUrl.value = "/assets/img/dummy_headshot.png";
            } finally {
                isLoadingImage.value = false; // Loading is complete
            }
        } else {
            userImageUrl.value = "/assets/img/dummy_headshot.png";
        }
    },
    { immediate: true }
);
</script>
<style scoped>
.highlight:before {
    content: "";
    width: 100%;
    height: 100%;
    display: block;
    position: absolute;
    left: 0;
    top: 0;
    /* background-image: linear-gradient(to top, #add8e611, #fffb); */
    background-image: linear-gradient(to top, #add8e6, #fff);
    opacity: 0.3;
    box-shadow: 0px 0px 10px #000;
    z-index: -1;
}
</style>
<template>
    <AppHead
        pageTitle="Login"
        :description="'Login to ' + usePage<any>().props.site.name + ''"
        :url="route('auth.login.page')"
    />
    <Navbar />
    <Sidebar>
        <FlashMessages />
        <div class="cell large-4 medium-6 small-12">
            <div class="align-middle flex-container align-center">
                <div class="gap-2 mb-4 text-center align-center">
                    <v-lazy-image
                        :src="userImageUrl"
                        class="space-image mb-2"
                        width="150"
                        alt="Headshot"
                        src-placeholder="/assets/img/dummy_headshot.png"
                    />
                    <div
                        class="text-center text-2xl fw-semibold"
                        style="line-height: 16px"
                    >
                        <span> {{ form.username }} </span>
                    </div>
                </div>
            </div>
            <form @submit.prevent="submit">
                <div class="mb-2">
                    <div
                        :class="{ 'text-danger': form.errors.username }"
                        class="text-xs fw-bold text-muted text-uppercase"
                    >
                        Username
                    </div>
                    <input
                        type="text"
                        v-model="form.username"
                        class="form"
                        placeholder="Username..."
                    />
                    <div
                        v-if="form.errors.username"
                        class="text-xs text-danger fw-semibold"
                    >
                        {{ form.errors.username }}
                    </div>
                </div>
                <div class="mb-2">
                    <div
                        :class="{ 'text-danger': form.errors.password }"
                        class="text-xs fw-bold text-muted text-uppercase"
                    >
                        Password
                    </div>
                    <div class="gap-2 align-middle flex-container-lg">
                        <input
                            type="password"
                            v-model="form.password"
                            class="mb-2 form"
                            placeholder="Password..."
                        />
                        <div
                            v-if="form.errors.password"
                            class="text-xs text-danger fw-semibold"
                        >
                            {{ form.errors.password }}
                        </div>
                        <div class="mb-2 flex-container flex-child-grow">
                            <button
                                type="submit"
                                class="btn btn-circle btn-info"
                                v-bind:class="{ 'is-loading': ConfirmingAuth }"
                                :disabled="form.processing"
                                @click="ConfirmUserAuth"
                                value="Log In"
                            >
                                <i
                                    class="fa-duotone fa-solid fa-arrow-right"
                                ></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="mx-1 my-3 divider"></div>

                <div class="align-middle flex-container align-center">
                    <Link
                        as="button"
                        :href="route('auth.forgot.page')"
                        class="btn btn-info btn-sm text-sm fw-semibold squish"
                        >Forgot Password?</Link
                    >
                </div>
            </form>
        </div>
    </Sidebar>
    <Footer />
</template>
