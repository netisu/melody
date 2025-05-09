<script setup lang="ts">
import Navbar from '@/Components/LayoutParts/Navbar.vue';
import Sidebar from '@/Components/LayoutParts/Sidebar.vue';
import ForumSidebar from '@/Components/LayoutParts/ForumSidebar.vue';
import Footer from '@/Components/LayoutParts/Footer.vue';
import AppHead from '@/Components/AppHead.vue';
import VLazyImage from "v-lazy-image";
import { route } from "ziggy-js"; // If you're using the 'route' function from 'momentum-trail'
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

defineProps({
    posts: { type: Object },
    section_one: { type: Object },
    section_two: { type: Object },
    section_three: { type: Object },
    topic: {
        type: Object,
        required: true,
    },
});


const canPost = () => computed(() => {
    const sectionData = usePage<any>().props.topic;
    return usePage<any>().props.auth.user.positionID >= sectionData.role_required_to_post;
});
</script>

<template>
    <AppHead pageTitle="Discuss"
        :description="'Here, You can discuss with the community surrounding several topics on ' + usePage<any>().props.site.name + '.'"
        :url="route('forum.page', { id: usePage<any>().props.topic.id })" />

    <Navbar />
    <Sidebar>
        <div class="cell large-2">
            <ForumSidebar :topic="topic" :section_one="section_one" :section_two="section_two"
                :section_three="section_three" />
        </div>
        <div class="cell large-10">
            <div class="mb-2 grid-x grid-margin-x">
                <div class="cell large-5">
                        <div class="text-xl fw-semibold">{{ usePage<any>().props.topic.name }}</div>
                        <div class="text-xs text-muted fw-semibold">{{ usePage<any>().props.topic.description }}</div>
                </div>
                <div class="cell large-7" v-if="usePage<any>().props.auth.user">
                    <div class="gap-2 align-middle flex-container-lg">

                        <input type="text" class="flex-container mb-2 form form-xs form-has-section-color"
                            placeholder="Search..." name="search" />

                        <div class="mb-2 flex-container flex-child-grow">
                            <Link :href="route(`forum.your.posts`)" as="button"
                                class="text-center btn btn-info btn-xs flex-child-grow"><i
                                class="fad fa-star-circle"></i>&nbsp; Your Posts</Link>
                        </div>
                        <div class="mb-2 flex-container flex-child-grow">
                            <Link as="button"
                                v-if="canPost"
                                :href="route(`forum.create.page`, { id: usePage<any>().props.topic.id })"
                                :class="(usePage<any>().props.auth.user.staff ? 'btn-danger' : 'btn-success')"
                                class="text-center btn btn-xs flex-child-grow"><i class="fad fa-hexagon-plus"></i>&nbsp;
                            Create Post</Link>
                        </div>
                    </div>
                </div>
            </div>
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
                                <p class="text-xs">There are currently no posts in this category.</p>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3 row thread" v-for="(post, index) in usePage<any>().props.posts.data">
                        <div class="mx-1 my-3" :class="{ 'divider': index !== 0 }"></div>
                        <div class="gap-2 align-middle flex-container">
                            <Link :href="route('user.profile', { username: post.creator.username })">
                            <v-lazy-image :src="post.creator.headshot" width="65"
                                class="border headshot flex-child-shrink img-fluid rounded-circle border-secondary bg-dark"
                                alt="Avatar" src-placeholder="/assets/img/dummy_headshot.png" />
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
                                    <Link :href="route('user.profile', { username: post.creator.username })"
                                        class="text-sm fw-semibold text-body">
                                    {{ post.creator.display_name }} &bullet; {{ post.DateHum }}
                                    </Link>
                                    <div class="text-xs text-muted fw-semibold text-body">
                                        {{ '@' + post.creator.username }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div :class="{ divider: usePage<any>().props.posts.data.length > 0 }" class="mx-1 my-3"></div>
                    <Pagination v-if="usePage<any>().props.posts.data.length" class="mx-1 my-3"
                        :pagedata="usePage<any>().props.posts"></Pagination>
                </div>

            </div>
        </div>
    </Sidebar>
    <Footer />
</template>
