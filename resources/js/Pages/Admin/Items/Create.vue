<script setup lang="ts">
import { ref } from "vue";
import axios from "axios";
import { route } from 'ziggy-js';

import { useForm, usePage } from "@inertiajs/vue3";
import Sidebar from "@/Components/LayoutParts/Sidebar.vue";
import Navbar from "@/Components/LayoutParts/Navbar.vue";
import AppHead from "@/Components/AppHead.vue";
import AdminNav from "@/Components/LayoutParts/Admin/AdminNav.vue";
import Footer from "@/Components/LayoutParts/Footer.vue";

const Uploading = ref(false);

const form = useForm({
    type: "",
    name: "",
    description: "",
    price_sparkles: "",
    price_Stars: "",
    image: null,
    modal: null,
});
const submit = () => {
    axios.get(`/sanctum/csrf-cookie`).then(() => {
        form.post(route(`admin.items.create.validate`), {
            onFinish: () => form.reset("description"),
        });
    });
};

const AttemptUpload = () => {
    Uploading.value = true;
};
const handleImageUpload = (event) => {
    form.image = event.target.files[0];
};
const handleModalUpload = (event) => {
    form.modal = event.target.files[0];
};
</script>

<template>
    <AppHead pageTitle="Create Item" description="Create a brand new item."
        :url="route('admin.items.create.create-item')" />
    <Navbar>
        <Sidebar>
            <div class="cell medium-3">
            <AdminNav />
        </div>
        <div class="cell medium-7">
                <div class="mb-2">
                    <div class="text-2xl fw-semibold">Create Super Item</div>
                </div>
                <div></div>
                <div class="card card-body">
                    <form @submit.prevent="submit">
                        <div :class="{ 'text-danger': form.errors.type }"
                            class="text-xs fw-bold text-muted text-uppercase">
                            Item Type
                        </div>
                        <div class="select title is-6 is-fullwidth">
                            <select class="form form-select" v-model="form.type">
                                <option value="hat">Hat</option>
                                <option value="addon">Add-on</option>
                                <option value="tool">Tool</option>
                                <option value="face">Face</option>
                                <option value="head">Head</option>
                            </select>
                            <div v-if="form.errors.type" class="text-xs text-danger fw-semibold">
                                {{ form.errors.type }}
                            </div>
                        </div>
                        <div class="mb-2">
                            <div :class="{ 'text-danger': form.errors.name }"
                                class="text-xs fw-bold text-muted text-uppercase">
                                Item Name
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
                                <div class="text-xs fw-bold text-muted text-uppercase">Price sparkles</div>
                                <input type="text" v-model="form.sparkles" class="form" placeholder="1" />
                                <div v-if="form.errors.sparkles" class="text-xs text-danger fw-semibold">
                                    {{ form.errors.sparkles }}
                                </div>
                            </div>
                            <div class="w-100">
                                <div class="text-xs fw-bold text-muted text-uppercase">Price Stars</div>
                                <input type="text" v-model="form.Stars" class="form" placeholder="10" />
                                <div v-if="form.errors.Stars" class="text-xs text-danger fw-semibold">
                                    {{ form.errors.Stars }}
                                </div>
                            </div>
                        </div>
                        <div class="gap-3 text-sm flex-container align-center">
                            <div class="w-100">
                                <div class="text-xs fw-bold text-muted text-uppercase">Image</div>
                                <input class="form text-body" @input="form.image = $event.target.files[0]"
                                    type="file" />
                                <div v-if="form.errors.image" class="text-xs text-danger fw-semibold">
                                    {{ form.errors.image }}
                                </div>
                            </div>
                            <div class="w-100" v-if="form && form.type !== 'face'">
                                <div class="text-xs fw-bold text-muted text-uppercase">Modal</div>
                                <input class="form text-body" @change="handleModalUpload($event)" type="file" />

                                <div v-if="form.errors.image" class="text-xs text-danger fw-semibold">
                                    {{ form.errors.image }}
                                </div>
                            </div>
                        </div>
                        <span v-if="form.image">
                            File Output {{ form.image.name }}
                        </span>
                        <span v-if="form.modal">
                            File Output {{ form.modal.name }}
                        </span>
                        <button :disabled="form.processing" @click="AttemptUpload"
                            v-bind:class="{ 'is-loading': Uploading }" type="submit" class="mt-2 btn btn-info w-100">
                            <i class="fad fa-cloud">&nbsp;</i>
                            Upload</button>
                    </form>
                </div>
            </div>
            <div class="cell medium-2">
            </div>
        </Sidebar>
    </Navbar>
    <Footer />

</template>
