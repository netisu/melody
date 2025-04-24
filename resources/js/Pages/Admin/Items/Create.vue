<script setup lang="ts">
import { ref } from "vue";
import axios from "axios";
import { route } from "momentum-trail"

import { useForm } from "@inertiajs/vue3";
import Navbar from "@/Components/LayoutParts/Admin/AdminNav.vue";
import AppHead from "@/Components/AppHead.vue";

const Uploading = ref(false);

const form = useForm({
    type: "",
    name: "",
    description: "",
    price_coins: "",
    price_bucks: "",
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
        <div class="columns">
            <div class="column is-3"></div>
            <div class="column">
                <br />
                <p class="title is-4">Create Item</p>
                <div class="card">
                    <div class="card-content">
                        <form @submit.prevent="submit">
                            <label><strong>Item Type</strong></label>
                            <br />
                            <div class="select title is-6 is-fullwidth">
                                <select v-model="form.type">
                                    <option value="hat">Hat</option>
                                    <option value="addon">Add-on</option>
                                    <option value="tool">Tool</option>
                                    <option value="face">Face</option>
                                    <option value="head">Head</option>
                                </select>
                                <div v-if="form.errors.type" class="subtitle text-danger">
                                    {{ form.errors.type }}
                                </div>
                            </div>
                            <br />
                            <label><strong>Item Name</strong></label>
                            <br />
                            <div class="title is-6 is-fullwidth">
                                <input v-model="form.name" class="input" placeholder="Item Name">
                                <div v-if="form.errors.name" class="subtitle text-danger">
                                    {{ form.errors.name }}
                                </div>
                            </div>
                            <p class="title is-6">Item Description</p>
                            <div class="field">
                                <p class="control">
                                    <textarea v-model="form.description" class="textarea has-fixed-size"
                                        placeholder="Description" />
                                <div v-if="form.errors.description" class="subtitle text-danger">
                                    {{ form.errors.description }}
                                </div>
                                </p>
                            </div>
                            <div class="columns">
                                <div class="column is-6">
                                    <div class="title is-6 is-fullwidth">
                                        Cost Coins
                                    </div>
                                    <input v-model="form.price_coins" class="input" type="text"
                                        placeholder="Cost Coins" />
                                    <div v-if="form.errors.price_coins" class="subtitle text-danger">
                                        {{ form.errors.price_coins }}
                                    </div>
                                </div>
                                <div class="column is-6 is-fullwidth">
                                    <div class="title is-6 is-fullwidth">
                                        Cost Bucks
                                    </div>
                                    <input v-model="form.price_bucks" class="input" type="text"
                                        placeholder="Cost Bucks" />
                                    <div v-if="form.errors.price_bucks" class="subtitle text-danger">
                                        {{ form.errors.price_bucks }}
                                    </div>
                                </div>
                            </div>
                            <div class="content">
                                <div class="columns">
                                    <div class="column is-6"
                                        :class="{ 'is-6': form && form.type !== 'face', 'is-12': form && form.type === 'face' }">
                                        <div class="title is-6 is-fullwidth">
                                            Image
                                        </div>
                                        <input class="input" @change="handleImageUpload($event)" type="file" />
                                        <div v-if="form.errors.image"
                                            class="has-text-weight-semibold is-size-6 has-text-danger">
                                            {{ form.errors.image }}
                                        </div>
                                    </div>
                                    <div class="column is-6 is-fullwidth" v-if="form && form.type !== 'face'">
                                        <div class="title is-6 is-fullwidth">
                                            Modal
                                        </div>
                                        <input class="input" @change="handleModalUpload($event)" type="file" />
                                        <div v-if="form.errors.modal" class="subtitle text-danger">
                                            {{ form.errors.modal }}
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
                                    v-bind:class="{ 'is-loading': Uploading }" type="submit"
                                    class="button has-text-white is-danger is-fullwidth">
                                    <i class="fas fa-cloud">&nbsp;</i>
                                    Upload</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="column is-3"></div>
        </div>
    </Navbar>
</template>
