<script setup lang="ts">
import AppHead from "@/Components/AppHead.vue";
import Navbar from "@/Components/LayoutParts/Navbar.vue";
import Sidebar from "@/Components/LayoutParts/Sidebar.vue";

import Footer from "@/Components/LayoutParts/Footer.vue";
import { ref } from "vue";
import { useForm } from "@inertiajs/vue3";
import { route } from 'ziggy-js';

import axios from "axios";

const Uploading = ref(false);

const form = useForm({
    name: "",
    description: "",
    image: "",
});

const submit = () => {
    axios.get(`/sanctum/csrf-cookie`).then(() => {
        form.post(route(`spaces.create.validate`), {
            onFinish: () => form.reset("description"),
        });
    });
};

const AttemptUpload = () => {
    Uploading.value = true;
};
</script>
<template>
    <AppHead pageTitle="Create" description="Create A Space Here." :url="route(`spaces.create.page`)" />
    <Navbar />
    <Sidebar>
        <div class="cell medium-5">
            <div class="mb-2">
                <div class="text-2xl fw-semibold">Create Space</div>
            </div>
            <div></div>
            <div class="card card-body">
                <form @submit.prevent="submit">
                    <div class="mb-2">
                        <div :class="{ 'text-danger': form.errors.name }"
                            class="text-xs fw-bold text-muted text-uppercase">
                            Space Name
                        </div>
                        <input type="text" v-model="form.name" class="form" placeholder="Space Name..." />
                        <div v-if="form.errors.name" class="text-xs text-danger fw-semibold">
                            {{ form.errors.name }}
                        </div>
                    </div>
                    <div class="mb-2">
                        <div :class="{ 'text-danger': form.errors.description }"
                            class="text-xs fw-bold text-muted text-uppercase">
                            Description
                        </div>
                        <textarea type="password" v-model="form.description" class="mb-2 form pe-5" rows="5"
                            placeholder="Description...."></textarea>
                        <div v-if="form.errors.description" class="text-xs text-danger fw-semibold">
                            {{ form.errors.description }}
                        </div>
                    </div>
                    <div class="gap-3 text-sm flex-container align-center">
                        <div class="w-100">
                            <div class="text-xs fw-bold text-muted text-uppercase">Icon</div>
                            <input class="form text-body" @input="form.image = $event.target.files[0]" type="file" />
                            <div v-if="form.errors.image" class="text-xs text-danger fw-semibold">
                                {{ form.errors.image }}
                            </div>
                        </div>
                    </div>

                    <div class="mx-1 my-3 divider"></div>
                    <button :disabled="form.processing" @click="AttemptUpload"
                        v-bind:class="{ 'is-loading': Uploading }" value="Upload"
                        class="my-2 mt-2 btn btn-success btn-block">
                        <i class="fab fa-cloud-arrow me-1"></i>
                        Upload
                    </button>
                </form>
            </div>
        </div>
    </Sidebar>
    <Footer />
</template>
