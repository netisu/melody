<script setup lang="ts">
import Navbar from '@/Components/LayoutParts/Navbar.vue';
import Sidebar from '@/Components/LayoutParts/Sidebar.vue';
import Footer from '@/Components/LayoutParts/Footer.vue';
import AeoPagination from '@/Components/Pagination.vue';
import { route } from "momentum-trail"

import { ref } from 'vue';
import axios from 'axios';
import { useForm, usePage, usePoll } from '@inertiajs/vue3';
import VLazyImage from 'v-lazy-image';
import SuperBanner from '@/Components/LayoutParts/SuperBanner.vue';

defineProps<{
    topic?: Object,
    post?: Array<any>,
    replies?: Array<any>,
}>()

const form = useForm({
    body: '',
});

const submit = () => {
    axios.get(`/sanctum/csrf-cookie`).then(() => {
        form.post(route(`forum.reply.validate`, { id: usePage<any>().props.post.id }), {
            onFinish: () => form.reset('body'),
        });
    });
};

const isHidden = ref(true);
usePoll(10000, { only: ['replies'] })
</script>

<style>
.headerDivider {
    border-left: 1px solid #38546d;
    border-right: 1px solid #16222c;
    height: 80px;
    position: absolute;
    right: 249px;
    top: 10px;
}
</style>
<template>
    <Navbar />
    <Sidebar image="/assets/img/site-banners/market-background.svg" :superBanActive="true"
        :OfficialImageBackground="true">
        <template #SuperBanner>
            <SuperBanner>
                <template #bannerAsset>
                    <v-lazy-image :style="usePage<any>().props.post.creator?.settings.calling_card_enabled
                        ? {
                            margin: '0',
                            'background-image':
                                'url(' +
                                usePage<any>().props.post.creator?.settings.calling_card_url +
                                ')',
                            'background-repeat': 'no-repeat',
                            'background-size': 'cover',
                            'box-shadow':
                                'inset 0 0 0 100vw rgba(var(--section-bg-rgb), 0.5)!important',
                        }
                        : null
                        " :src="usePage<any>().props.post.creator.headshot" width="100" class="space-image"
                        alt="Headshot" src-placeholder="assets/img/space-error.png" />
                </template>

                <template #bannerName>
                    <div class="text-xl" style="line-height: 16px">
                        {{ usePage<any>().props.post.title }}
                    </div>
                </template>
                <template #bannerDescription>
                    <div class="text-start">
                        <div class=" text-xl fw-semibold"><span v-if="usePage<any>().props.post.pinned"><i
                                    class="text-danger fas fa-thumbtack"></i></span> <span
                                v-if="usePage<any>().props.post.locked"><i class="text-warning fas fa-lock"></i></span>
                        </div>
                        <div class=" text-xs text-base fw-semibold">Posted by: {{ usePage<any>
                                ().props.post.creator.username }} <span>&bullet;</span> {{ usePage<any>
                                    ().props.post.DateHum
                                    }} </div>
                    </div>
                </template>
                <template #bannerButtons>
                    <Link class="text-xl text-success squish" href="#" content="Edit Post" as="button"
                        v-tippy="{ placement: 'bottom' }" style="outline: none">
                    <i class="fad fa-square-pen"></i>
                    </Link>
                </template>
            </SuperBanner>
        </template>
        <div class="cell medium-12">
            <div class="card-body">
                <div class="grid-x grid-margin-x">
                    <div class="cell medium-2">
                        <div class="gap-2 mb-3 text-center align-center">
                            <Link
                                :href="route('user.profile', { username: usePage<any>().props.post.creator.username })">
                            <v-lazy-image :src="usePage<any>().props.post.creator.thumbnail" width="136px"
                                class="border img-fluid" :alt="usePage<any>().props.post.creator.username"
                                src-placeholder="/assets/img/dummy.png" /> </Link>
                            <div class="text-center" style="line-height: 16px">
                                <div class="fw-semibold  text-truncate">{{ usePage<any>
                                        ().props.post.creator.display_name }}
                                </div>
                                <div class="text-xs fw-semibold text-muted text-truncate">
                                    {{ '@' + usePage<any>().props.post.creator.username }}
                                </div>
                                <div class="text-center">

                                    <div v-if="usePage<any>().props.post.creator.staff"
                                        class="mt-1 mb-1 badge badge-danger fw-semibold w-100">
                                        {{ usePage<any>().props.post.creator.position }}
                                    </div>

                                    <div v-else-if="usePage<any>().props.post.creator.settings.beta_tester"
                                        class="mt-1 mb-1 badge badge-success fw-semibold w-100">
                                        Beta Tester
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="cell medium-10">
                        <div class="gap-1 flex-container flex-dir-column">
                            <div v-html="usePage<any>().props.post.body" style="margin:0px;white-space: pre-wrap;">
                            </div>
                        </div>
                    </div>
                </div>
                <div v-if="usePage<any>().props.replies.data.length">
                    <div class="mx-1 my-3 divider"></div>
                    <div v-for="reply in usePage<any>().props.replies.data" class="grid-x grid-margin-x">
                        <div class="cell medium-2">
                            <div class="gap-2 mb-3 text-center align-center">
                                <Link :href="route('user.profile', { username: reply.creator.username })">
                                <v-lazy-image :src="reply.creator.thumbnail" width="136px" class="border img-fluid"
                                    :alt="reply.creator.username" src-placeholder="/assets/img/dummy.png" />
                                </Link>
                                <div class="text-center" style="line-height: 16px">
                                    <div class="fw-semibold text-truncate">{{ reply.creator.display_name }}
                                    </div>
                                    <div class="text-xs fw-semibold text-muted text-truncate">
                                        {{ '@' + reply.creator.username }}
                                    </div>
                                </div>
                                <div class="text-center">

                                    <div v-if="reply.creator.staff"
                                        class="mt-1 mb-1 badge badge-danger fw-semibold w-100">
                                        {{ reply.creator.position }}
                                    </div>

                                    <div v-else-if="reply.creator.settings.beta_tester"
                                        class="mt-1 mb-1 badge badge-success fw-semibold w-100">
                                        Beta Tester
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="cell medium-10">
                            <div class=" flex-container flex-dir-column">
                                <div class="text-sm text-muted fw-semibold"><i class="fas fa-clock"></i>&nbsp;
                                    {{ reply.DateHum }}</div>
                                <div v-html="reply.body" style="white-space: pre-wrap;"></div>
                            </div>
                        </div>
                    </div>



                </div>
                <AeoPagination v-if="usePage<any>().props.replies.data.length"
                    v-bind:pagedata="usePage<any>().props.replies" />
            </div>
            <div v-if="!isHidden" class="cell medium-9">
                <div class="card-body">
                    <div class="text-xl text-body fw-semibold bg-primary">Reply to {{ usePage<any>().props.post.title }}
                    </div>
                    <div class="mx-1 my-2 divider"></div>
                    <form @submit.prevent="submit">
                        <label for="post-body" class="mb-2 text-xs fw-bold text-uppercase">Reply Body</label>
                        <div class="position-relative"><textarea id="post-body" name="body" v-model="form.body"
                                class="mb-2 form form-has-button form-has-section-color pe-5" maxlength="4096" rows="5"
                                placeholder="Post your thoughts here."></textarea>
                            <input type="submit" :disabled="form.processing" class="btn btn-success btn-sm has-ripple"
                                value="Post" style="position: absolute; bottom: 10px; right: 10px;">
                        </div>
                    </form>
                </div>
            </div>
            <div class="section flex-container align-center">
                <a @click="isHidden = !isHidden" v-if="usePage<any>().props.auth.user" class="btn btn-success"><i
                        class="fas fa-reply"></i>&nbsp;Reply</a>
            </div>
        </div>
    </Sidebar>
    <Footer />
</template>
