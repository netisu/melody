<script setup lang="ts">
import axios from "axios";
import { route } from "momentum-trail"

import { usePage } from "@inertiajs/vue3";
import Navbar from "@/Components/LayoutParts/Admin/AdminNav.vue";
import AppHead from "@/Components/AppHead.vue";

const ApproveAsset = async () => {
    await axios
        .post(route(`api.admin.enableMaintenance`))
        .catch((err) => console.log(err));
};

const DenyAsset = async () => {
    await axios
        .post(route(`api.admin.disableMaintenance`))
        .catch((err) => console.log(err));
};
</script>

<template>
    <AppHead pageTitle="Admin Dashboard" :description="usePage<any>().props.site.name + ' Administration'"
        :url="route('admin.page')" />
    <Navbar>
        <div class="card">
            <div class="card-content">
                <p class="title is-4">Content Approval</p>
                <br />


                <article class="media" v-for="item in usePage<any>().props.pendingAssets">
                    <figure class="media-left">
                        <p class="image is-128x128">
                            <img :src="item.data.thumbnail" />
                        </p>
                    </figure>
                    <div class="media-content">
                        <div class="content">
                            <p class="title is-4">{{ item.data.name }}</p>
                            <p class="subtitle is-6">{{ item.data.description }}</p>
                            <div class="buttons">
                                <a class="button">Approve</a>
                                <a class="button is-danger">Deny</a>
                            </div>
                        </div>
                    </div>
                    <div class="media-right">
                        <p class="title is-6">Type: {{ item.type }}</p>
                        <p class="title is-6">Created: {{ item.data.DateHum }}</p>
                        <p class="subtitle is-6">Created by:
                            <Link :href="route('admin.users.manage-user', { id: item.data.creator.id })"
                                class="has-text-link">{{
                            item.data.creator.username }}</Link>
                        </p>
                        <div class="media-content">
                            <div class="content">
                                <Link class="button" :href="route(`admin.items.manage-item`, { id: item.data.id })">View
                                Item</Link>
                            </div>
                        </div>
                    </div>
                </article>

            </div>
        </div>
    </Navbar>
</template>
