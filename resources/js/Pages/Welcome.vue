<script setup lang="ts">
import AppHead from "@/Components/AppHead.vue";
import Navbar from "@/Components/LayoutParts/Navbar.vue";
import Footer from "@/Components/LayoutParts/Footer.vue";
import { computed } from "vue";
import { usePage } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import StarsBg from "@/Images/site-banners/stars-bg.png";
import DummyError from "@/Images/dummy-error.png";

const defaultAvatar = StarsBg;

const handleImageError = (event) => {
    if (event.target.src !== StarsBg) {
        event.target.src = StarsBg;
    }
};

const site = computed<any>(() => usePage<any>().props.site);

defineProps({
    landing: { type: Object },
});

function onImgErrorSmall(id) {
    let source = document.getElementById(id) as HTMLImageElement;
    source.src = DummyError;
    source.onerror = null;

    return true;
}
</script>
<style scoped src="@/Pages/landing.css"></style>
<template>
    <AppHead pageTitle="Welcome"
        description="Join in on the action today. Buy &amp; sell items, participate in spaces, make friends, and more."
        :url="route(`welcome.page`)" />
    <Navbar :landing="true">
        <header class="masthead-landing p-4 pt-4">
            <img :src="landing?.['user']?.['avatar'] ?? defaultAvatar" @error="handleImageError" style="z-index: 1"
                alt="background" />
            <div class="cell large-4" style="position: relative; z-index: 3">
                <div class="min-w-0 gap-4 ml-4 flex-container row-landing">
                    <div class="row-landing">
                        <div class="text-white">
                            <!-- Page heading-->
                            <div class="text-5xl fw-semibold">
                                {{ $t("welcome.message") }}
                            </div>
                            <h3 class="mt-4 text-wrap fw-semibold">
                                {{ $t("welcome.desc") }}
                            </h3>
                            <div class="min-w-0 gap-2 mt-3 flex-container">
                                <Link :href="route('auth.register.page')" class="btn btn-landing btn-success">
                                <i class="mr-1 fa-duotone fa-person-to-portal show-for-medium"></i>
                                Get Started
                                </Link>
                                <Link :href="route('auth.login.page')" class="btn btn-landing btn-info">
                                <i class="mr-1 fa-duotone fa-door-open show-for-medium"></i>
                                Login
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>
        <main class="container container-landing">
            <div class="justify-center grid-x grid-margin-x align-center grid-padding-y">
                <div class="cell medium-12 pb-2">
                    <div class="pb-2">
                        <h3 class="text-lg font-medium">
                            <div class="text-xl fw-semibold">
                                Customize your Character
                            </div>
                        </h3>
                        <p class="text-sm text-muted">
                            Pick and choose from a collection of items created
                            by the community to create you own unique character
                            with.
                        </p>
                    </div>
                    <div class="divider mx-1 my-1 mb-2" />
                </div>

                <div class="cell large-12">
                    <div v-if="
                        landing?.['items']?.['data'] &&
                        landing?.['items']?.['data'].length > 5
                    " class="grid-x grid-margin-x grid-padding-y">
                        <div class="cell large-2 medium-4 small-6" v-for="item in landing?.['items']?.['data']">
                            <Link :href="route(`store.item`, { id: item.id })" class="d-block">
                            <div class="p-2 mb-1 card card-item position-relative">
                                <img :src="item.thumbnail" :id="item.name" :alt="item.name" class="img-fluid"
                                    @error="onImgErrorSmall(item.name)" />
                            </div>
                            <div class="text-body fw-semibold text-truncate">
                                {{ item.name }}
                            </div>
                            </Link>
                            <Link :href="route(`user.profile`, {
                                username: item.creator.username,
                            })
                                " class="text-info">
                            <div class="text-xs text-muted">
                                By: {{ "@" + item.creator.username }}
                            </div>
                            </Link>
                        </div>
                    </div>
                    <div v-else>
                        <div class="card">
                            <div class="card-body">
                                <div class="gap-2 text-center flex-container flex-dir-column">
                                    <i class="text-5xl fad fa-shirt text-muted"></i>
                                    <div class="text-lg fw-semibold">
                                        No items uploaded.
                                    </div>
                                    <div class=" text-sm text-muted">
                                        There are no items available.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="cell medium-12 pb-2 ">
                    <div class="pb-2 ">
                        <h3 class="text-lg font-medium">
                            <div class="text-xl fw-semibold">
                                Socialize With Others
                            </div>
                        </h3>
                        <p class="text-sm text-muted">
                            Head over to Discussion to socialize with all kinds
                            of users on
                            {{ site.name }}, from the funny to the professional,
                            and find new friends!
                        </p>
                    </div>
                    <div class="divider mx-1 my-1 mb-2" />
                </div>
                <div class="cell large-12">

                    <div v-if="
                        landing?.['posts']?.['data'] &&
                        landing?.['posts']?.['data'].length
                    " class="grid-x grid-margin-x grid-padding-y">
                        <div class="mb-1 cell large-3 medium-6 small-6" v-for="post in landing?.['posts']?.['data']">
                            <div class="gap-2 mb-3 card card-body flex-container flex-dir-column">
                                <div class="gap-2 flex-container">
                                    <div class="gap-2 flex-container">
                                        <button class="gap-2 align-middle flex-container squish">
                                            <img class="headshot" :src="post.creator.headshot" width="50" height="50"
                                                alt="Avatar" />
                                        </button>
                                        <div>
                                            <p class="fw-semibold text-truncate">
                                                {{ post.title }}
                                            </p>
                                            <div class="text-sm fw-semibold text-muted">
                                                By:
                                                <Link :href="route(
                                                    `user.profile`,
                                                    {
                                                        username:
                                                            post
                                                                .creator
                                                                .username,
                                                    }
                                                )
                                                    " class="text-info">{{
                                                        post.creator
                                                            .username
                                                    }}</Link>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="gap-4 my-3 mb-1 flex-container align-center flex-dir-column">
                                    <div class="text-sm text-muted fw-semibold text-truncate">
                                        <span v-html="post.body" />
                                    </div>
                                </div>
                                <div class="divider-m0 w-100"></div>
                                <Link :href="route(`forum.post`, {
                                    id: post.id,
                                    slug: post.slug,
                                })
                                    " class="min-w-0 w-100 text-center btn btn-info btn-sm text-truncate">View
                                Post</Link>
                            </div>
                        </div>
                    </div>
                    <div v-else>
                        <div class="card">
                            <div class="card-body">
                                <div class="gap-2 text-center flex-container flex-dir-column">
                                    <i class="text-5xl fad fa-comment text-muted"></i>
                                    <div class="text-lg fw-semibold">
                                        No Posts Created
                                    </div>
                                    <div class=" text-sm text-muted">
                                        There are no posts available.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
        <Footer />
    </Navbar>
</template>
