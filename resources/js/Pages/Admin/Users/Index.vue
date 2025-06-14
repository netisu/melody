<script setup lang="ts">
import { route } from 'ziggy-js';

import { usePage } from "@inertiajs/vue3";
import Navbar from "@/Components/LayoutParts/Navbar.vue";
import Footer from "@/Components/LayoutParts/Footer.vue";
import AppHead from "@/Components/AppHead.vue";
import Pagination from "@/Components/Pagination.vue";
import Sidebar from "@/Components/LayoutParts/Sidebar.vue";
import AdminNav from "@/Components/LayoutParts/Admin/AdminNav.vue";
import VLazyImage from "v-lazy-image";
</script>

<template>
    <AppHead pageTitle="User Index" description="View all users here." :url="route(`admin.users.page`)" />
    <Navbar />
    <Sidebar>
        <div class="cell medium-3">
            <AdminNav />
        </div>
        <div class="cell medium-9">
            <div class="grid-x grid-margin-x">
                <div class="cell medium-6 mb-2" v-for="user in usePage<any>().props.users.data"
                    :key="usePage<any>().props.users.id">
                    <div class="card card-body">
                        <div class="grid-x grid-margin-x">

                            <div class="gap-2 cell small-7 medium-9 flex-container">
                                <Link :href="route('admin.users.manage', { username: user.username })">
                                <v-lazy-image :src="user.headshot" width="45px"
                                    class="border img-fluid headshot rounded-circle border-secondary bg-dark"
                                    :alt="user.username" />
                                </Link>
                                <div class="flex-wrap col-md-4 d-flex justify-content-center align-content-start"
                                    style="flex-direction: column">
                                    <div class="text-start">
                                        <Link :href="route('admin.users.manage', { username: user.username })"
                                            class="text-md">
                                        {{ user.display_name }}
                                        </Link>
                                        <div></div>
                                        <Link :href="route('admin.users.manage', { username: user.username })"
                                            class="text-sm fw-semibold text-body">
                                        {{ '@' + user.username }}
                                        </Link>
                                        <div></div>
                                    </div>
                                </div>
                            </div>
                            <div class="cell show-for-large small-5 medium-3 flex-container align-right gap-2"
                                v-if="user.isStaff || user.isBanned">
                                <div v-if="user.isStaff"
                                    :content='user.username + " works at " + usePage<any>().props.site.name' v-tippy
                                    class="badge badge-info" style="height:25px;">Staff</div>
                                <div v-if="user.isBanned" :content='user.username + " is banned"'   v-tippy
                                    class="badge badge-success" style="height:25px;">Banned
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div v-if="!usePage<any>().props.users?.['data']?.['length']" class="text-danger fw-semibold">ERR T100:
                No
                users
                found</div>
            <div v-if="usePage<any>().props.users?.['data']?.['length']" class="mx-3 my-3 divider"></div>
            <Pagination v-if="usePage<any>().props.users?.['data']?.['length']"
                v-bind:pagedata="usePage<any>().props.users">
            </Pagination>
        </div>
    </Sidebar>
    <Footer />
</template>
