<script setup lang="ts">
import { ref } from "vue";
import { Link, usePage } from "@inertiajs/vue3";
import axios from "axios";
import { route } from 'ziggy-js';

import Button from "@/Components/Button.vue";
import Navbar from "@/Components/LayoutParts/Navbar.vue";
import AppHead from "@/Components/AppHead.vue";
import Footer from "@/Components/LayoutParts/Footer.vue";
import Sidebar from "@/Components/LayoutParts/Sidebar.vue";
import VLazyImage from "v-lazy-image";
import SuperBanner from '@/Components/LayoutParts/SuperBanner.vue';
import DummyError from "@/Images/dummy-error.png";

defineProps({
    spaces: { type: Array, required: true },
});

const JoinSpace = () => {
    axios.get(`/sanctum/csrf-cookie`).then(() => {
        axios.post(route(`spaces.membership`, { id: usePage<any>().props.space.id }), {
        });
    });
};

const LeaveSpace = () => {
    axios.get(`/sanctum/csrf-cookie`).then(() => {
        axios.post(route(`spaces.membership`, { id: usePage<any>().props.space.id }), {
        });
    });
};
</script>
<template>
    <AppHead pageTitle="Spaces" description="Find a space to join or create one!" :url="route(`spaces.page`)" />
    <Navbar />
    <Sidebar :superBanActive="true" :alertsEnabled="true">
        <template #SuperBanner>
            <SuperBanner>
                <template #bannerAsset>
                    <v-lazy-image :src="usePage<any>().props.space.thumbnail ?? DummyError"
                        width="100" class="space-image" alt="Space Thumbnail"
                        :src-placeholder="DummyError" />
                </template>
                <template #bannerName>
                    <div class="text-xl" style="line-height: 16px">
                        {{ usePage<any>().props.space.name }}
                    </div>
                </template>
                <template #bannerDescription>
                    <p class="text-muted">{{ usePage<any>().props.space.description }}</p>
                </template>
                <template #bannerButtons>
                    <Link v-if="usePage<any>().props.auth.user && !usePage<any>().props.isInSpace"
                        class="text-xl text-success squish" href="#" @click="JoinSpace"
                        :content="'Join ' + usePage<any>().props.space.name" as="button"
                        v-tippy="{ placement: 'bottom' }" style="outline: none;">
                    <i class="fad fa-user-plus"></i>
                    </Link>
                    <Link
                        v-if="usePage<any>().props.auth.user && usePage<any>().props.auth.user.id != usePage<any>().props.space.creator.id  && usePage<any>().props.isInSpace"
                        class="text-xl text-danger squish" href="#" @click="LeaveSpace"
                        :content="'Leave ' + usePage<any>().props.space.name" as="button"
                        v-tippy="{ placement: 'bottom' }" style="outline: none;">
                    <i class="fad fa-user-minus"></i>
                    </Link>
                </template>
            </SuperBanner>
        </template>
        <div class="cell medium-3">
            <div class="mb-1 text-xl fw-semibold">Actions</div>
            <div class="gap-3 mt-3 mb-3 align-middle">
                <ul class="tabs flex-dir-column">

                    <li class="tab-item">
                        <a class="tab-link squish active">
                            <i class="fa-solid fa-users"></i>
                            General
                        </a>
                    </li>
                    <li class="tab-item">
                        <a class="tab-link squish">
                            <i class="fa-solid fa-store"></i>
                            Shop
                        </a>
                    </li>
                    <li class="tab-item">
                        <a class="tab-link squish">
                            <i class="fa-solid fa-gamepad-alt"></i>
                            Games
                        </a>
                    </li>
                </ul>

            </div>
            <div class="mb-1 text-xl fw-semibold">Statistics</div>
            <div class="mb-3 card card-body">
                <div class="gap-1 align-middle flex-container flex-dir-column">

                    <div class="text-sm text-info w-100">
                        <i class="text-center fas fa-users-crown" style="width: 26px"></i>
                        {{ usePage<any>().props.space.creator.username }}
                    </div>
                    <div class="text-sm w-100">
                        <i class="text-center fas fa-user-group text-muted" style="width: 26px"></i>
                        {{ usePage<any>().props.space.members.count + " Members" }}
                    </div>
                    <div class="text-sm w-100">
                        <i class="text-center fas fa-clock text-muted" style="width: 26px"></i>
                        {{ usePage<any>().props.space.DateHum }}
                    </div>
                </div>
            </div>
        </div>
        <div class="cell medium-9">
            <div style="height: 6px"></div>
            <div class="mb-3 card card-body">
                <div class="section">
                    <ul class="tabs">
                        <li class="tab-item">
                            <a href="#" class="tab-link active squish">Members</a>
                        </li>
                        <li class="tab-item">
                            <a href="#" class="tab-link squish">Posts</a>
                        </li>
                    </ul>
                </div>
                <div class="section">
                    <div class="mb-2 align-middle grid-x">
                        <div class="cell large-3">
                            <div class="mb-2 text-xl fw-semibold">
                                Members
                            </div>
                        </div>
                        <div class="cell large-9">
                            <div class="gap-2 align-middle flex-container-lg">
                                <input type="text" class="mb-2 form form-xs form-has-section-color"
                                    placeholder="Search Members...">
                                <select class="mb-2 form form-xs form-select form-has-section-color">
                                    <option value="1" selected>
                                        Members
                                    </option>
                                    <option value="2">
                                        Administrators
                                    </option>
                                    <option value="3" disabled>
                                        Creators
                                    </option>

                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="flex-wrap gap-3 mt-2 mb-2 flex-container">
                        <div class="min-w-0 text-center" style="width: 80px">
                            <img src="/assets/img/dummy_headshot.png" class="headshot flex-child-grow" alt="headshot">
                            <div class="text-sm text-info fw-semibold text-truncate">
                                Aeo
                            </div>
                        </div>
                        <div class="min-w-0 text-center" style="width: 80px">
                            <img src="/assets/img/dummy_headshot.png" class="headshot flex-child-grow" alt="headshot">
                            <div class="text-sm text-danger fw-semibold text-truncate">
                                Aeo
                            </div>
                        </div>
                        <div class="min-w-0 text-center" style="width: 80px">
                            <img src="/assets/img/dummy_headshot.png" class="headshot flex-child-grow" alt="headshot">
                            <div class="text-sm fw-semibold text-truncate">
                                Aeo
                            </div>
                        </div>
                    </div>

                </div>
                <div class="section">
                    <ul class="pagination flex-container align-center">
                        <button class="page-link page-disabled">
                            <i class="fa-regular fa-chevron-left"></i>
                        </button>
                        <button class="page-link page-active">1</button>
                        <button class="page-link">2</button>
                        <button class="page-link">3</button>
                        <button class="page-link">
                            <i class="fa-regular fa-chevron-right"></i>
                        </button>
                    </ul>
                </div>
            </div>
        </div>
    </Sidebar>
    <Footer />
</template>
