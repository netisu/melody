<script setup lang="ts">
import Navbar from '@/Components/LayoutParts/Navbar.vue';
import AppHead from '@/Components/AppHead.vue';
import Footer from '@/Components/LayoutParts/Footer.vue';
import Sidebar from '@/Components/LayoutParts/Sidebar.vue';
import { route } from "momentum-trail"

import { Link, usePage } from '@inertiajs/vue3';
import VLazyImage from "v-lazy-image";
</script>

<template>
    <AppHead pageTitle="Users" description="View all users here." :url="route(`user.page`)" />
    <Navbar />
    <Sidebar>
        <div class="cell medium-3 small-12">
            <div class="text-3xl fw-semibold">Discover</div>
            <div class="mb-2 text-md text-muted">Find new friends among {{ usePage<any>().props.user_count }}
                    users.</div>

            <ul class="tabs flex-dir-column">
                <li class="tab-item">
                    <a class="tab-link squish active">
                        All
                    </a>
                </li>
            </ul>
        </div>
        <div class="cell medium-9">
            <div class="mb-2 text-md text-muted fw-semibold ">Users</div>
            <div class="grid-x grid-margin-x">
                <div class="cell medium-6 mb-2" v-for="user in usePage<any>().props.users.data"
                    :key="usePage<any>().props.users.id">
                    <div class="card card-body card-status" :class="[user.online ? 'online' : 'offline']">
                        <div class="grid-x grid-margin-x">

                            <div class="gap-2 cell small-7 medium-9 flex-container">
                                <Link :href="route('user.profile', { username: user.username })">
                                <v-lazy-image :src="user.avatar" width="45px"
                                    class="border img-fluid headshot rounded-circle border-secondary bg-dark"
                                    :alt="user.username" />
                                </Link>
                                <div class="flex-wrap col-md-4 d-flex justify-content-center align-content-start"
                                    style="flex-direction: column">
                                    <div class="text-start">
                                        <Link :href="route('user.profile', { username: user.username })"
                                            class="text-md">
                                        {{ user.dname }}
                                        </Link>
                                        <div></div>
                                        <Link :href="route('user.profile', { username: user.username })"
                                            class="text-sm fw-semibold text-body">
                                        {{ '@' + user.username }}
                                        </Link>
                                        <div></div>
                                    </div>
                                </div>
                            </div>
                            <div class="cell show-for-large small-5 medium-3 flex-container align-right gap-2"
                                v-if="user.staff || user.settings.beta_tester">
                                <div v-if="user.staff"
                                    :content='user.username + " works at " + usePage<any>().props.site.name' v-tippy
                                    class="badge badge-info" style="height:25px;">Staff</div>
                                <div v-if="user.settings.beta_tester" :content='user.username + " is a beta tester"' v-tippy
                                    class="badge badge-success" style="height:25px;">Tester
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
<script lang="ts">
import { directive } from "vue-tippy";

export default {
    directives: {
        tippy: directive,
    },
}
</script>
