<script setup lang="ts">
import Navbar from '@/Components/LayoutParts/Navbar.vue';
import Sidebar from '@/Components/LayoutParts/Sidebar.vue';
import Footer from '@/Components/LayoutParts/Footer.vue';
import { route } from "ziggy-js";
import VLazyImage from 'v-lazy-image';

import { usePage, useForm } from '@inertiajs/vue3';
import axios from 'axios';

const form = useForm({
    code: "",
});

const submit = () => {
    axios.get(`/sanctum/csrf-cookie`).then(() => {
        form.post(route(`promocodes.redeem`));
    });
};
</script>

<template>
    <Navbar>
        <Sidebar>
            <div class="cell medium-12">
                <div class="mb-3  card-body">
                    <div class="mb-3">
                        <div class="fw-semibold text-2xl text-info mb-2">
                            <i class="fad fa-ticket"></i> Redeem Promocode
                        </div>
                        <div class="divider mx-1 my-2"></div>
                        <form @submit.prevent="submit">

                            <div class="gap-2 align-middle flex-container-lg">
                                <input v-model="form.code" type="text" class="mb-2 form form-has-section-color"
                                    placeholder="Promocode..." />

                                <div class="mb-2 flex-container flex-child-grow">
                                    <button type="submit" :disabled="form.processing"
                                        class="btn  btn-info w-100">Redeem</button>
                                </div>
                            </div>
                            <div v-if="form.errors.code" class="text-xs text-danger fw-semibold">
                                {{ form.errors.code }}
                            </div>
                        </form>

                    </div>
                </div>
                <div class="mb-3 card-body">
                    <div class="mb-3">
                        <p class="fw-semibold text-xl">
                            Available Promocode Items
                        </p>
                        <div class="divider mx-1 my-2"></div>
                        <p class="fw-semibold text-sm text-muted">
                            These exclusive items will not be available forever, so make sure to redeem them before they
                            expire!
                        </p>
                        <div class="mt-2 mb-2" v-for="item in usePage<any>().props.items">
                            <div class="gap-2 align-middle flex-container">
                                <v-lazy-image :src="item.thumbnail" width="100" height="70" />
                                <div class="flex-wrap col-md-4 d-flex justify-content-center align-content-start"
                                    style="flex-direction: column;">
                                    <div class="text-start">
                                        <Link class="text-md" :href="route(`store.item`, { id: item.id })">
                                        {{ item.name }}
                                        </Link>
                                        <div></div>
                                        <p class="text-sm fw-semibold">
                                            {{ 'Expires on: ' + item.expiry }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </Sidebar>
    </Navbar>
    <Footer />
</template>
