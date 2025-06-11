<script lang="ts" setup>
import Navbar from '@/Components/LayoutParts/Navbar.vue';
import Sidebar from '@/Components/LayoutParts/Sidebar.vue';
import Footer from '@/Components/LayoutParts/Footer.vue';
import { usePage } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import DummyHeadshot from "@/Images/dummy_headshot.png";

import VLazyImage from "v-lazy-image";
defineProps({
    posts: { type: Object },
});
</script>
<template>
    <Navbar />
    <Sidebar>
        <div class="cell medium-10">
            <div class="text-xl text-base bg-primary mb-2">My Posts</div>
            <div class="card">
                <div class="pb-0 card-body">
                    <div class="gap-3 text-center flex-container flex-dir-column"
                        v-if="!usePage<any>().props.posts.data.length">
                        <i class="text-5xl fad fa-message-xmark text-muted"></i>
                        <div style="line-height: 16px">
                            <div class="text-xs fw-bold text-muted text-uppercase">
                                No Forum Posts
                            </div>
                            <div class="text-muted fw-semibold">
                                <p class="text-xs">You have no posts.</p>
                                <Link :href="route(`forum.create.page`, { id: 4 })" class="text-xs">New Post</Link>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3 row thread" v-for="(post, index) in usePage<any>().props.posts.data">
                        <div class="mx-1 my-3" :class="{ 'divider': index !== 0 }"></div>
                        <div class="gap-2 align-middle flex-container">
                            <Link :href="route('user.profile', { username: post.username })">
                            <v-lazy-image :src="post.headshot" width="65"
                                class="border headshot flex-child-shrink img-fluid rounded-circle border-secondary bg-dark"
                                alt="Avatar" :src-placeholder="DummyHeadshot" />
                            </Link>
                            <div class="flex-wrap col-md-4 d-flex justify-content-center align-content-start"
                                style="flex-direction: column">
                                <div class="text-start">
                                    <Link :href="route('forum.post', { id: post.id, slug: post.seo })" class="text-md">
                                    {{ post.name }} <span v-if="post.pinned"><i
                                            class="text-danger fas fa-thumbtack"></i></span> <span v-if="post.locked"><i
                                            class="text-warning fas fa-lock"></i></span>
                                    </Link>
                                    <div></div>
                                    <Link :href="route('user.profile', { username: post.username })"
                                        class="text-sm fw-semibold text-body">
                                    {{ post.display_name }} &bullet; {{ post.DateHum }}
                                    </Link>
                                    <div class="text-xs text-muted fw-semibold text-body">
                                        @{{ post.username }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div :class="{ divider: usePage<any>().props.posts.data.length > 0 }" class="mx-1 my-3"></div>
                </div>
            </div>
        </div>
    </Sidebar>
    <Footer />
</template>
