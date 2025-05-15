<script setup lang="ts">
import AppHead from "@/Components/AppHead.vue";
import Navbar from "@/Components/LayoutParts/Navbar.vue";
import Sidebar from "@/Components/LayoutParts/Sidebar.vue";

import Footer from "@/Components/LayoutParts/Footer.vue";
import { ref } from "vue";
import { usePage, useForm } from "@inertiajs/vue3";
import { route } from 'ziggy-js';
import VLazyImage from "v-lazy-image";

import axios from "axios";


const Uploading = ref(false);
const item = usePage<any>().props.item;

const form = useForm({
    name: "",
    description: "",
    cost_sparkles: "",
    cost_Stars: "",
    isOnsale: true,
});

const submit = () => {
    axios.get(`/sanctum/csrf-cookie`).then(() => {
        form.post(route(`store.edit.validate`, { id: item.id }), {
            onFinish: () => form.reset("description"),
        });
    });
};

const AttemptUpload = () => {
    Uploading.value = true;
};
</script>
<template>
    <AppHead pageTitle="Edit Item" :description="'Edit' + item.name + '.'"
        :url="route(`store.edit`, { id: item.id })" />
    <Navbar />
    <Sidebar>
        <div class="cell medium-4">
            <div class="mb-2">
                <div class="text-2xl fw-semibold">Item Thumbnail</div>
            </div>
            <div></div>
            <div class="card card-body">
                <v-lazy-image :src="item.thumbnail" />
            </div>
        </div>
        <div class="cell medium-5">
            <div class="mb-2">
                <div class="text-2xl fw-semibold">{{ 'Edit ' + item.name }}</div>
            </div>
            <div></div>
            <div class="card card-body">
                <form @submit.prevent="submit">
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
                        <textarea type="password" v-model="form.description" class="mb-2 form pe-5" rows="5"
                            placeholder="Description...."></textarea>
                        <div v-if="form.errors.description" class="text-xs text-danger fw-semibold">
                            {{ form.errors.description }}
                        </div>
                    </div>
                    <div class="gap-3 mb-2 text-sm flex-container align-center">
                        <div class="w-100">
                            <div class="text-xs fw-bold text-muted text-uppercase">Price sparkles</div>
                            <input type="text" v-model="form.cost_sparkles" class="form" placeholder="1" />
                            <div v-if="form.errors.cost_sparkles" class="text-xs text-danger fw-semibold">
                                {{ form.errors.cost_sparkles }}
                            </div>
                        </div>
                        <div class="w-100">
                            <div class="text-xs fw-bold text-muted text-uppercase">Price Stars</div>
                            <input type="text" v-model="form.cost_Stars" class="form" placeholder="10" />
                            <div v-if="form.errors.cost_Stars" class="text-xs text-danger fw-semibold">
                                {{ form.errors.cost_Stars }}
                            </div>
                        </div>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" v-model="form.isOnsale"  type="checkbox" value=""
                            id="flexCheckChecked" checked>
                        <label class="form-check-label" for="flexCheckChecked">
                            Onsale
                        </label>
                    </div>
                    <div class="mx-1 my-3 divider"></div>
                    <button :disabled="form.processing" @click="AttemptUpload"
                        v-bind:class="{ 'is-loading': Uploading }" value="Upload"
                        class="my-2 mt-2 btn btn-success btn-block">
                        <i class="fad fa-pen me-1"></i>
                        Edit Item
                    </button>
                </form>
            </div>
        </div>
    </Sidebar>
    <Footer />
</template>
