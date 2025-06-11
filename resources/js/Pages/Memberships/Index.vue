<script setup lang="ts">
import AppHead from "@/Components/AppHead.vue";
import Navbar from "@/Components/LayoutParts/Navbar.vue";
import Sidebar from "@/Components/LayoutParts/Sidebar.vue";

import Footer from "@/Components/LayoutParts/Footer.vue";
import { computed, onMounted, ref } from "vue";
import { usePage } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import DummyHeadshot from "@/Images/dummy_headshot.png";


const site = computed<any>(() => usePage<any>().props.site);

defineProps({
    LandingItems: { type: Object },
    LandingPosts: { type: Object },
});
const Welcome = [
    {
        en: {
            message: `Welcome to ${usePage<any>().props.site.name}!`,
            desc: `${
                usePage<any>().props.site.name
            } is a collaborative 3D sandbox community. Work together to build amazing things and have fun along the way!`,
        },
        es: {
            message: `Bienvenido a ${usePage<any>().props.site.name}!`,
            desc: `${
                usePage<any>().props.site.name
            } es una comunidad colaborativa de sandbox 3D. ¡Trabajad juntos para construir cosas increíbles y divertíos en el camino!`,
        },
        ru: {
            message: `Добро пожаловать в ${usePage<any>().props.site.name}!`,
            desc: `${
                usePage<any>().props.site.name
            }— это совместное сообщество 3D-песочниц. Работайте вместе, чтобы создавать удивительные вещи, и получайте от этого удовольствие!`,
        },
        ja: {
            message: `${usePage<any>().props.site.name}へようこそ！`,
            desc: `${
                usePage<any>().props.site.name
            } は、協力的な 3D サンドボックス コミュニティです。協力して素晴らしいものを作り、その過程を楽しみましょう!`,
        },
    },
];
const lang = computed<any>(() => usePage<any>().props.locale);

onMounted(() => {
    showLoading();
});

function onImgErrorSmall(id) {
    let source = document.getElementById(id) as HTMLImageElement;
    source.src = "/assets/img/dummy-error.png";
    source.onerror = null;

    return true;
}
</script>
<template>
    <AppHead pageTitle="Welcome"
        description="Join in on the action today. Buy &amp; sell items, participate in spaces, make friends, and more."
        :url="route(`welcome.page`)" :landing="true"  />
    <Navbar :landing="true" />
        <Sidebar>
        <header class="masthead-landing p-4 pt-4">
            <img src="/assets/img/site-banners/stars-bg.png" style="z-index: 1" alt="background" />
            <div class="cell large-4" style="position: relative; z-index: 3">
                <div class="min-w-0 gap-4 ml-4 flex-container row-landing">
                    <div class="row-landing">
                        <div class="text-white">
                            <!-- Page heading-->
                            <span class="text-5xl fw-semibold">
                                <span v-for="messages in Welcome">
                                    {{ messages[lang].message }}
                                </span>
                            </span>
                            <!--h1 class="mb-5">
            <v-lazy-image :src="site.logo" width="300px" />
          </h1-->
                            <h3 class="p-2 mt-3 text-wrap fw-semibold" v-for="desc in Welcome">
                                {{ desc[lang].desc }}
                            </h3>
                            <div class="min-w-0 gap-2 mt-3 flex-container">
                                <Link :href="route('auth.register.page')" class="btn btn-landing btn-success">
                                <i class="mr-1 fa-solid fa-person-to-portal show-for-medium"></i>
                                Get Started
                                </Link>
                                <Link :href="route('auth.login.page')" class="btn btn-landing btn-info">
                                <i class="mr-1 fa-solid fa-person-to-portal show-for-medium"></i>
                                Login
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>
        <nav v-if="usePage<any>().props.site_config.announcement" class="alert alert-danger alert-landing">
            <div class="py-1 text-center   fw-semibold">
                <div class="gap-2 text-center align-middle flex-container align-center">
                    <div>
                        {{ usePage<any>().props.site_config.announcement_message }}
                    </div>
                </div>
            </div>
        </nav>
        <main class="container">
            <div class="justify-center grid-x grid-margin-x align-center grid-padding-y">
                <div v-if="LandingItems?.['data'] || LandingItems?.['data'].length < 5" class="cell large-12">
                    <div class="align-middle grid-x">
                        <div class="p-2 mb-4 cell large-4">
                            <div class="landing-text-landing fw-semibold">
                                Customize your Character
                            </div>
                            <div>
                                Pick and choose from a collection of items
                                created by the community to create you own
                                unique character with.
                            </div>
                        </div>
                        <div class="cell large-8">
                            <div class="grid-x grid-margin-x grid-padding-y">
                                <div class="cell large-2 medium-4 small-6" v-for="item in LandingItems?.['data']">
                                    <Link :href="
                                            route(`store.item`, { id: item.id })
                                        " class="d-block">
                                    <div class="p-2 mb-1 card card-item position-relative">
                                        <img :src="item.thumbnail" :id="item.name" :alt="item.name" class="img-fluid"
                                            @error="
                                                    onImgErrorSmall(item.name)
                                                " />
                                    </div>
                                    <div class="text-body fw-semibold text-truncate">
                                        {{ item.name }}
                                    </div>
                                    </Link>
                                    <div class="text-xs fw-semibold">
                                        <span class="text-muted">By </span>
                                        <Link :href="
                                                route(`user.profile`, {
                                                    username:
                                                        item.creator.username,
                                                })
                                            " class="text-info">{{ "@" + item.creator.username }}
                                        </Link>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="cell large-12">
                    <div class="mb-1 align-middle grid-x">
                        <div class="cell large-8">
                            <div class="grid-x grid-margin-x grid-padding-y">

                                <div class="mb-1 cell large-3 medium-6 small-6" v-for="post in LandingPosts?.['data']">
                                    <div class="gap-2 mb-3 card card-body flex-container flex-dir-column">
                                        <div class="gap-2 flex-container">
                                            <div class="gap-2 flex-container">
                                                <button class="gap-2 align-middle flex-container squish">
                                                    <img class="headshot" :src="DummyHeadshot" width="50"
                                                        height="50" alt="Avatar" />
                                                </button>
                                                <div>
                                                    <p class="fw-semibold text-truncate">
                                                        {{ post.title }}
                                                    </p>
                                                    <div class="text-sm fw-semibold text-muted">
                                                        By:
                                                        <Link
                                                            :href="route(`user.profile`, { username: post.creator.username })"
                                                            class="text-info">{{ post.creator.username }}</Link>
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
                                        <Link :href="route(`forum.post`, {id: post.id, slug: post.slug})"
                                            class="min-w-0 w-100 text-center btn btn-info btn-sm text-truncate">View
                                        Post</Link>
                                    </div>
                                </div>

                            </div>
                        </div>
                        <div class="p-5 mt-4 cell large-4">
                            <div class="landing-text-landing fw-semibold">
                                Socialize With Others
                            </div>
                            <div>
                                Head over to Discussion to socialize with all
                                kinds of users on
                                {{ site.name }}, from the funny to the
                                professional, and find new friends!
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
        <Footer :isLanding="true" />
    </Sidebar>
</template>
