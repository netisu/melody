<script setup lang="ts">
import AppHead from "@/Components/AppHead.vue";
import Navbar from "@/Components/LayoutParts/Navbar.vue";
import Sidebar from "@/Components/LayoutParts/Sidebar.vue";

import Footer from "@/Components/LayoutParts/Footer.vue";
import { ref } from "vue";
import { usePage, useForm } from "@inertiajs/vue3";
import { route } from 'ziggy-js';

import axios from "axios";


const Uploading = ref(false);

const form = useForm({
    type: "",
    name: "",
    description: "",
    sparkles: "",
    stars: "",
    image: "",
});

const submit = () => {
    axios.get(`/sanctum/csrf-cookie`).then(() => {
        form.post(route(`store.create.validate`), {
            onFinish: () => form.reset("description"),
        });
    });
};


const AttemptUpload = () => {
    Uploading.value = true;
};
</script>
<template>
    <AppHead pageTitle="Create" description="create your dream item here" :url="route(`store.create.page`)" />
    <Navbar />
    <Sidebar>
        <div class="cell medium-6 px-4">
            <div class="mb-2">
                <div class="text-2xl fw-semibold">Create Item</div>
                <div class="text-sm text-danger fw-semibold" v-show="usePage<any>().props.auth.user.staff">
                    <i class="fad fa-exclamation-triangle"></i> Staff can create content here, but for privileged items,
                    use the admin portal.
                </div>

                <div class="text-sm text-muted fw-semibold" v-show="!usePage<any>().props.auth.user.staff">
                    Need the template? Download it
                    <a href="#" class="d-inline-block squish">here</a>
                </div>
            </div>
            <div></div>
            <div class="card card-body">
                <form @submit.prevent="submit">
                    <div class="mb-2">
                        <div :class="{ 'text-danger': form.errors.type }"
                            class="text-xs fw-bold text-muted text-uppercase">
                            Item Type
                        </div>
                        <select class="form form-select" v-model="form.type">
                            <option value="shirt">Shirt</option>
                            <option value="tshirt">T-shirt</option>
                            <option value="pants">Pants</option>
                        </select>
                        <div v-if="form.errors.type" class="text-xs text-danger fw-semibold">
                            {{ form.errors.type }}
                        </div>
                    </div>
                    <div class="mb-2">
                        <div :class="{ 'text-danger': form.errors.name }"
                            class="text-xs fw-bold text-muted text-uppercase">
                            Name
                        </div>
                        <input type="text" v-model="form.name" class="form" placeholder="Item Name..." />
                        <div v-if="form.errors.name" class="text-xs text-danger fw-semibold">
                            {{ form.errors.name }}
                        </div>
                    </div>
                    <div class="mb-2">
                        <div :class="{ 'text-danger': form.errors.description }"
                            class="text-xs fw-bold text-muted text-uppercase">
                            Description
                        </div>
                        <textarea v-model="form.description" class="mb-2 form pe-5" rows="3"
                            placeholder="Description...."></textarea>
                        <div v-if="form.errors.description" class="text-xs text-danger fw-semibold">
                            {{ form.errors.description }}
                        </div>
                    </div>
                    <div class="gap-3 mb-2 text-sm flex-container align-center">
                        <div class="w-100">
                            <div class="text-xs fw-bold text-muted text-uppercase">Price Sparkles</div>
                            <input type="text" v-model="form.sparkles" class="form" placeholder="1" />
                            <div v-if="form.errors.sparkles" class="text-xs text-danger fw-semibold">
                                {{ form.errors.sparkles }}
                            </div>
                        </div>
                        <div class="w-100">
                            <div class="text-xs fw-bold text-muted text-uppercase">Price Stars</div>
                            <input type="text" v-model="form.stars" class="form" placeholder="10" />
                            <div v-if="form.errors.stars" class="text-xs text-danger fw-semibold">
                                {{ form.errors.stars }}
                            </div>
                        </div>
                    </div>
                    <div class="gap-3 text-sm flex-container align-center">
                        <div class="w-100">
                            <div class="text-xs fw-bold text-muted text-uppercase">Image</div>
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
