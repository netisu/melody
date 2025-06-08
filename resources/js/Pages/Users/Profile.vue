<script setup lang="ts">
import axios from 'axios';
import { ref, watch, onMounted } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { route } from 'ziggy-js';

import VLazyImage from "v-lazy-image";
import Footer from '@/Components/LayoutParts/Footer.vue';
import Navbar from '@/Components/LayoutParts/Navbar.vue';
import Sidebar from '@/Components/LayoutParts/Sidebar.vue';
import AppHead from '@/Components/AppHead.vue';
import ItemCardSkeleton from '@/Components/ItemCardSkeleton.vue';
import type { Ref } from 'vue';
import SuperBanner from '@/Components/LayoutParts/SuperBanner.vue';

defineProps({
    user: { type: Object },
    is_following: { type: Boolean },
    statuses: { type: Object },
    categorizedItems: { type: Object },

});

let following = ref(usePage<any>().props.is_following); // Initial follow status

const toggleFollow = (id) => {
    if (following.value) {
        // Execute the unfollow logic here
        unfollowUser(id);
    } else {
        // Execute the follow logic here
        followUser(id);
    }
};

const items = ref<any>([]);
const CurentlyWearingItems = ref<any>([]);

const ItemLoading = ref(false);

function onImgErrorSmall(id) {
    let source = document.getElementById(id) as HTMLImageElement;
    source.src = "/assets/img/dummy-error.png";
    source.onerror = null;

    return true;
}
// Fetch items for the selected category
async function fetchItems() {
    try {
        ItemLoading.value = true;
        const response = await axios.get(
            route(`api.user.inventory`, { id: usePage<any>().props.user.id })
        );
        items.value = response.data;
        ItemLoading.value = false;
    } catch (error) {
        console.error(error);
    }
}
async function fetchCurrentlyWearing() {
    try {
        ItemLoading.value = true;
        const response = await axios.get(
            route(`api.user.currently-wearing`, { id: usePage<any>().props.user.id })
        );
        CurentlyWearingItems.value = response.data;
        ItemLoading.value = false;
    } catch (error) {
        console.error(error);
    }
}
const itemRoute = (itemId) => route(`store.item`, { id: itemId });
const creatorRoute = (username) => route(`user.profile`, { username: username });

const ActiveCategory: Ref<string> = ref("Inventory");

function setActiveCategory(category): void {
    ActiveCategory.value = category;
};

const showSuccessMessage = (message) => {

    console.log('Success:', message);
};
const capitalized = (name: string) => {
    const capitalizedFirst = name[0].toUpperCase();
    const rest = name.slice(1);

    return capitalizedFirst + rest;
};
let followercount = ref(usePage<any>().props.user.followers_count);
const followUser = (id) => {
    // Send a POST request to follow the user
    // Update the API endpoint and payload based on your server-side implementation
    axios.get(route(`api.user.follow`, { user: id }))
        .then(response => {
            // Handle the success response here
            const successMessage = response.data.message;
            showSuccessMessage(successMessage);
            following.value = true;
            followercount.value + 1;
        })
        .catch(error => {
            // Handle the error response here
            console.error('Error following user:', error);
        });
};

const unfollowUser = (id) => {
    // Send a POST request to unfollow the user
    // Update the API endpoint and payload based on your server-side implementation
    axios.post(route(`api.user.unfollow`, { user: id }))
        .then(response => {
            // Handle the success response here
            const successMessage = response.data.message;
            showSuccessMessage(successMessage);
            following.value = false;
            followercount.value - 1;
        })
        .catch(error => {
            // Handle the error response here
            console.error('Error unfollowing user:', error);
        });
};

onMounted(() => {
    fetchItems(),
        fetchCurrentlyWearing()
});

watch(following, (newValue, oldValue) => {
    console.log("Following variable changed from", oldValue, "to", newValue);
});

</script>
<template>
    <AppHead :pageTitle="usePage<any>().props.user.username + '\'s Profile'"
        :description="usePage<any>().props.user.about_me"
        :url="route(`user.profile`, { username: usePage<any>().props.user.username })"
        :cover="usePage<any>().props.user.thumbnail" />
    <Navbar />
    <Sidebar :alertsEnabled="false"
        :image="usePage<any>().props.user.settings.profile_banner_enabled ? usePage<any>().props.user.settings.profile_banner_url : null"
        :superBanActive="true" :OfficialImageBackground="false">
        <template #SuperBanner>
            <SuperBanner>
                <template #bannerAsset>
                    <v-lazy-image :style="usePage<any>().props.user?.settings.calling_card_enabled
                        ? {
                            margin: '0',
                            'background-image':
                                'url(' +
                                usePage<any>().props.user?.settings.calling_card_url +
                                ')',
                            'background-repeat': 'no-repeat',
                            'background-size': 'cover',
                            'box-shadow':
                                'inset 0 0 0 100vw rgba(var(--section-bg-rgb), 0.5)!important',
                        }
                        : null
                        " :src="usePage<any>().props.user.headshot" width="100" class="space-image" alt="Headshot"
                        src-placeholder="/assets/img/dummmy_headshot.png" />
                </template>

                <template #bannerName>
                    <div class="text-xl" style="line-height: 16px">
                        <div class="mb-1 flex-container align-middle gap-1 fw-semibold">
                            {{ usePage<any>().props.user.display_name }}
                                <i v-show="usePage<any>().props.user.staff" class="fad fa-gavel text-danger"></i>
                                <i v-show="usePage<any>().props.user.settings.beta_tester"
                                    class="fad fa-hard-hat text-success"></i>
                                <v-lazy-image class="headshot"
                                    :src="'/assets/img/flags/' + usePage<any>().props.user.settings.country_code + '.svg'"
                                    alt="Country Flag" style="width: auto;height: 20px;"
                                    src-placeholder="/assets/img/flags/other/pirate.svg" />
                                <Link v-if="usePage<any>().props.user.settings.primarySpace"
                                    :href="route(`spaces.view`, { id: usePage<any>().props.user.settings.primarySpace.id, slug: usePage<any>().props.user.settings.primarySpace.slug })">
                                <v-lazy-image class="headshot"
                                    :src="usePage<any>().props.user.settings.primarySpace.thumbnail" alt="Country Flag"
                                    style="width: auto;height: 20px;"
                                    src-placeholder="/assets/img/flags/other/pirate.svg"
                                    v-tippy="{ placement: 'bottom' }"
                                    :content="usePage<any>().props.user.settings.primarySpace.name" />
                                </Link>
                        </div>
                        <div class="text-sm text-muted fw-semibold">
                            {{ "@" + usePage<any>().props.user.username }}
                        </div>
                    </div>
                </template>
                <template #bannerDescription>
                    <span class="text-muted text-wrap text-truncate fw-semibold">
                        {{ usePage<any>().props.user.status ?? 'This user does not have a status.' }}
                    </span>
                </template>
                <template #bannerButtons>
                    <div v-if="usePage<any>().props.auth.user && usePage<any>().props.user.id != usePage<any>().props.auth.user.id"
                        class="min-w-0 gap-2 mt-2 mb-3 flex-container">
                        <button :class="['text-xl', 'squish', following ? 'text-danger' : 'text-info']"
                            @click="toggleFollow(usePage<any>().props.user.id)"
                            :content="following ? 'Unfollow' : 'Follow'" v-tippy="{ placement: 'bottom' }">
                            <i :class="following ? 'fad fa-user-minus' : 'fad fa-user-plus'"></i>
                        </button>

                        <Link as="button" :href="route(`chat.messages`, { userID: usePage<any>().props.user.id })"
                            class="text-xl text-success squish"
                            :content="'Chat With ' + usePage<any>().props.user.display_name"
                            v-tippy="{ placement: 'bottom' }">
                        <i class="fad fa-message"></i>
                        </Link>

                        <a v-show="usePage<any>().props.auth.user.staff" as="button"
                            :href="route(`admin.users.manage-user`, { id: usePage<any>().props.user.id })"
                            class="text-xl text-danger squish" content="View in Panel"
                            v-tippy="{ placement: 'bottom' }">
                            <i class="fad fa-pen-to-square"></i>
                        </a>
                    </div>
                </template>
            </SuperBanner>
        </template>
        <div class="cell medium-3">
            <div :class="['card', 'card-body', 'card-status', usePage<any>().props.user.online ? 'online' : 'offline']"
                :style="usePage<any>().props.user.settings.calling_card_enabled
                    ? {
                        'background-image': 'url(' + usePage<any>().props.user.settings.calling_card_url + ')',
                        'background-repeat': 'no-repeat',
                        'background-size': 'cover',
                        'box-shadow':
                            'inset 0 0 0 100vw rgba(var(--section-bg-rgb), 0.5)!important',
                    }
                    : null">
                <v-lazy-image
                    :src="usePage<any>().props.user.thumbnail ? usePage<any>().props.user.thumbnail : '/assets/img/dummy.png'"
                    src-placeholder="/assets/img/dummy.png" />
                <div class="mt-2 text-sm text-center align-center text-info fw-semibold">
                    <div :class="usePage<any>().props.user.staff ? 'text-danger' : 'text-success'">
                        {{ usePage<any>().props.user.staff ? usePage<any>().props.user.position : 'Netizen' }}
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="mt-2 text-center">
                    <div class="gap-3 text-sm flex-container align-center">
                        <div class="min-w-0 px-0 text-center fw-semibold text-muted text-truncate">
                            <span class="text-body">{{ usePage<any>().props.user.following_count }}</span>
                            Following
                        </div>
                        <div class="min-w-0 px-0 text-center fw-semibold text-muted text-truncate">
                            <span class="text-body">{{ followercount }}</span>
                            Followers
                        </div>
                    </div>
                    <div v-if="usePage<any>().props.user.followsYou" class="text-sm text-info fw-semibold">
                        Follows you
                    </div>
                </div>
            </div>
            <Link v-if="usePage<any>().props.user.settings.secondarySpace"
                :href="route(`spaces.view`, { id: usePage<any>().props.user.settings.secondarySpace.id, slug: usePage<any>().props.user.settings.secondarySpace.slug })"
                class="gap-2 p-2 mt-1 mb-2 align-middle card card-inner flex-container">
            <img :src="usePage<any>().props.user.settings.secondarySpace.thumbnail" class="headshot" width="40" />
            <div class="min-w-0" style="line-height: 14px">
                <div class="text-xs text-truncate fw-bold text-muted text-uppercase">
                    Secondary Space
                </div>
                <div class="text-sm text-truncate fw-semibold text-body">
                    {{ usePage<any>().props.user.settings.secondarySpace.name }}
                </div>
            </div>
            </Link>
            <div class="mb-1 text-xl fw-semibold">About Me</div>
            <div class="mb-3 card card-body">
                {{ usePage<any>().props.user.about_me }}
            </div>
            <div class="mb-1 text-xl fw-semibold">Statistics</div>
            <div class="mb-3 card card-body">
                <div class="gap-1 align-middle flex-container flex-dir-column">
                    <div class="text-sm text-membership fw-semibold w-100">
                        <i class="text-center fad fa-rocket-launch text-membership" style="width: 26px"></i>
                        Premium Subscriber
                    </div>
                    <div class="text-sm w-100">
                        <i class="text-center fad fa-medal text-muted" style="width: 26px"></i>
                        {{ "Rank Lvl. " + usePage<any>().props.user.level }}
                    </div>
                    <div class="text-sm w-100">
                        <i class="text-center fad fa-users-medical text-muted" style="width: 26px"></i>
                        Joined on {{ usePage<any>().props.user.joindate }}
                    </div>
                    <div class="text-sm w-100">
                        <i class="text-center fad fa-clock text-muted" style="width: 26px"></i>
                        Last seen {{ usePage<any>().props.user.DateHum }}
                    </div>
                    <div class="text-sm w-100">
                        <i class="text-center fad fa-messages text-muted" style="width: 26px"></i>
                        {{ usePage<any>().props.user.posts }} Discussion Posts
                    </div>
                </div>
            </div>
        </div>
        <div class="cell medium-6">
            <div v-show="ActiveCategory === 'Posts'">
                <div class="mb-3 card card-body">
                    <div v-if="!statuses?.['data']?.['length']" class="section">
                        <div class="gap-3 text-center flex-container flex-dir-column">
                            <i class="text-5xl fad fa-face-sleeping text-muted"></i>
                            <div style="line-height: 16px">
                                <div class="text-xs fw-bold text-muted text-uppercase">
                                    No Posts
                                </div>
                                <div class="text-xs text-muted fw-semibold">
                                    {{ usePage<any>().props.user.username }} has not posted anything to their
                                        feed.
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-else v-for="status in statuses?.['data']"
                        class="gap-3 section flex-container flex-dir-column-sm">
                        <div class="mx-auto flex-child-grow" style="width: 100px">
                            <a href="#" class="text-sm text-center d-block squish">
                                <img src="/assets/img/dummy_headshot.png" class="mb-1 headshot" width="60" />
                                <div style="line-height: 16px">
                                    <div class="text-membership text-truncate">
                                        {{ status.dname }}
                                    </div>
                                    <div class="text-xs text-muted text-truncate">{{ '@' + status.name }}
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="card card-body card-inner w-100">
                            <div class="align-middle flex-container align-justify">
                                <div class="w-100">
                                    <div class="text-xs text-muted">
                                        <i class="far fa-clock me-1"
                                            style="vertical-align: middle;margin-top: -2.5px;font-size: 10px;"></i>
                                        {{ status.DateHum }}
                                    </div>
                                    <div>
                                        {{ status.message }}
                                    </div>
                                    <div class="text-sm" style="margin-left: -6px">
                                        <button class="btn-like active squish">
                                            <i class="far fa-heart"></i>1
                                        </button>
                                    </div>
                                </div>
                                <div class="dropdown ms-auto position-relative">
                                    <button class="btn-circle" style="margin-right: -10px">
                                        <i class="fad fa-ellipsis-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li class="dropdown-item">
                                            <a href="#" class="dropdown-link dropdown-link-has-icon">
                                                <i class="fad fa-flag dropdown-icon"></i> Report
                                            </a>
                                        </li>
                                        <div class="align-middle flex-container">
                                            <div class="dropdown-title">Moderation</div>
                                            <li class="divider flex-child-grow"></li>
                                        </div>
                                        <li class="dropdown-item">
                                            <a href="#" class="dropdown-link dropdown-link-has-icon text-danger">
                                                <i class="fad fa-trash text-danger dropdown-icon"></i>
                                                Delete
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="my-2 align-middle flex-container">
                                <div class="text-xs fw-bold text-muted text-uppercase">
                                    Comments
                                </div>
                                <div class="divider flex-child-grow"></div>
                            </div>
                            <div class="gap-2 mb-2 flex-container">
                                <input type="text" class="form form-sm form-has-section-color"
                                    placeholder="What are your thoughts on this post?" />
                                <input type="button" class="btn btn-success btn-sm" value="Post" />
                            </div>
                        </div>
                    </div>
                </div>
                <Pagination v-if="statuses?.['data']?.['length']" v-bind:pagedata="statuses" />
            </div>

            <div v-show="ActiveCategory === 'Inventory'">
                                <div>
                    <div v-if="ItemLoading || (CurentlyWearingItems && CurentlyWearingItems.length > 0)" class="grid-x grid-margin-x grid-padding-y">
                        <template v-if="ItemLoading">
                            <ItemCardSkeleton v-for="n in 6" :key="n" />
                        </template>
                        <div v-else class="cell large-3 medium-3 small-6" v-for="(item, index) in CurentlyWearingItems" :key="index">
                            <Link :href="itemRoute(item.id)" class="d-block">
                            <div class="p-2 mb-1 card card-item position-relative">
                                <div style="
                                    position: absolute;
                                    bottom: 10px;
                                    left: 10px;
                                ">
                                    <div v-if="item.in_event" class="mb-1 badge badge-warning fw-semibold">
                                        <i class="fad fa-calendar-star" style="width: 18px"></i>Event
                                    </div>
                                    <div v-if="item.rare" class="mb-1 badge badge-info fw-semibold">
                                        <i class="fad fa-comet" style="width: 18px"></i>Rare
                                    </div>
                                    <div v-if="item.sale_ongoing" class="mb-1 badge badge-danger fw-semibold">
                                        <i class="fad fa-badge-percent" style="width: 18px"></i>{{
                                            item.percent_off + "%" }} off
                                    </div>
                                </div>
                                <img :src="item.thumbnail" :id="item.thumbnail"
                                    @error="onImgErrorSmall(item.thumbnail)" />
                            </div>
                            <div class="text-body fw-semibold text-truncate">
                                {{ item.name }}
                            </div>
                            </Link>
                            <div class="text-xs fw-semibold text-truncate">
                                <span class="text-muted">By:</span>&nbsp;
                                <Link :href="creatorRoute(item.creator.username)">
                                {{ "@" + item.creator.username
                                }}<i class="fas fa-shield-check text-success ms-1"></i></Link>
                            </div>
                        </div>
                    </div>
                    <div v-else class="">
                        <div class="pb-0 card-body">
                            <div class="gap-3 mb-2 text-center flex-container flex-dir-column">
                                <i class="text-5xl fad fa-person-fairy text-muted"></i>
                                <div style="line-height: 16px">
                                    <div class="text-xs fw-bold text-muted text-uppercase">
                                        No Items
                                    </div>
                                    <div class="text-muted fw-semibold">
                                        <p class="text-xs">
                                            {{ usePage<any>().props.user.username }} isn't wearing anything.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="divider mx-1 my-4" />
                    <span class="text-xl fw-semibold">Inventory</span>
                    <div v-if="ItemLoading || (items && items.length > 0)" class="grid-x grid-margin-x grid-padding-y">
                        <template v-if="ItemLoading">
                            <ItemCardSkeleton v-for="n in 12" :key="n" />
                        </template>
                        <div v-else class="cell large-3 medium-3 small-6" v-for="(item, index) in items" :key="index">
                            <Link :href="itemRoute(item.id)" class="d-block">
                            <div class="p-2 mb-1 card card-item position-relative">
                                <div style="
                                    position: absolute;
                                    bottom: 10px;
                                    left: 10px;
                                ">
                                    <div v-if="item.in_event" class="mb-1 badge badge-warning fw-semibold">
                                        <i class="fad fa-calendar-star" style="width: 18px"></i>Event
                                    </div>
                                    <div v-if="item.rare" class="mb-1 badge badge-info fw-semibold">
                                        <i class="fad fa-comet" style="width: 18px"></i>Rare
                                    </div>
                                    <div v-if="item.sale_ongoing" class="mb-1 badge badge-danger fw-semibold">
                                        <i class="fad fa-badge-percent" style="width: 18px"></i>{{
                                            item.percent_off + "%" }} off
                                    </div>
                                </div>
                                <img :src="item.thumbnail" :id="item.thumbnail"
                                    @error="onImgErrorSmall(item.thumbnail)" />
                            </div>
                            <div class="text-body fw-semibold text-truncate">
                                {{ item.name }}
                            </div>
                            </Link>
                            <div class="text-xs fw-semibold text-truncate">
                                <span class="text-muted">By:</span>&nbsp;
                                <Link :href="creatorRoute(item.creator.username)">
                                {{ "@" + item.creator.username
                                }}<i class="fas fa-shield-check text-success ms-1"></i></Link>
                            </div>
                        </div>
                    </div>
                    <div v-if="!items.length" class="">
                        <div class="pb-0 card-body">
                            <div class="gap-3 mb-2 text-center flex-container flex-dir-column">
                                <i class="text-5xl fad fa-crate-empty text-muted"></i>
                                <div style="line-height: 16px">
                                    <div class="text-xs fw-bold text-muted text-uppercase">
                                        No Items
                                    </div>
                                    <div class="text-muted fw-semibold">
                                        <p class="text-xs">
                                            {{ usePage<any>().props.user.username }} has no items in their
                                                inventory.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-if="!items.length" class="mt-2 align-middle flex-container align-center">
                    <Link
                        as="button"
                        :href="route('user.inventory', {username: usePage<any>().props.user.username})"
                        class="btn btn-info btn-sm text-xs fw-semibold squish"
                        >More Items</Link
                    >
                </div>
            </div>
            <div v-show="ActiveCategory === 'Spaces'">
                <div class="mb-3 card card-body">
                    <div class="gap-3 text-center flex-container flex-dir-column">
                        <i class="text-5xl fad fa-planet-ringed text-muted"></i>
                        <div style="line-height: 16px">
                            <div class="text-xs fw-bold text-muted text-uppercase">
                                No Spaces
                            </div>
                            <div class="text-xs text-muted fw-semibold">
                                {{ usePage<any>().props.user.username }} has not joined any spaces.
                            </div>
                        </div>
                    </div>
                    <div class="grid-x grid-margin-x grid-padding-y">
                        <div class="text-center cell medium-4 small-4">
                            <a href="#" class="text-body">
                                <img width="100px" src="/assets/img/icon.png" class="mb-1" />
                                <div>
                                    <div class="text-sm fw-semibold text-truncate">
                                        Project Eclipse
                                    </div>
                                    <div class="text-xs text-muted fw-semibold">4 Members</div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div v-show="ActiveCategory === 'Followers'" class="my-2">
                <div v-if="!usePage<any>().props.user.followers?.['data']?.['length']" class="mb-3 card card-body">
                    <div class="gap-3 text-center flex-container flex-dir-column">
                        <i class="text-5xl fad fa-face-cry text-muted"></i>
                        <div style="line-height: 16px">
                            <div class="text-xs fw-bold text-muted text-uppercase">
                                No followers
                            </div>
                            <div class="text-xs text-muted fw-semibold">
                                {{ usePage<any>().props.user.username }} has no followers.
                            </div>
                        </div>
                    </div>
                </div>
                <div v-else class="grid-x grid-margin-x">
                    <div class="cell medium-6 mb-2"
                        v-for="(followerUser, index) in usePage<any>().props.user.followers.data" :key="index">
                        <div class="card card-body">
                            <div class="grid-x grid-margin-x">

                                <div class="gap-2 cell small-7 medium-9 flex-container">
                                    <Link :href="route('user.profile', { username: followerUser.username })">
                                    <v-lazy-image :src="followerUser.avatar" width="45px"
                                        class="border img-fluid headshot rounded-circle border-secondary bg-dark"
                                        :alt="followerUser.username" />
                                    </Link>
                                    <div class="flex-wrap col-md-4 d-flex justify-content-center align-content-start"
                                        style="flex-direction: column">
                                        <div class="text-start">
                                            <Link :href="route('user.profile', { username: followerUser.username })"
                                                class="text-md">
                                            {{ followerUser.display_name }}
                                            </Link>
                                            <div></div>
                                            <Link :href="route('user.profile', { username: followerUser.username })"
                                                class="text-sm fw-semibold text-body">
                                            {{ '@' + followerUser.username }}
                                            </Link>
                                            <div></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-show="usePage<any>().props.user.followers?.['data']?.['length']" class="mx-3 my-3 divider"></div>
                <Pagination v-show="usePage<any>().props.user.followers?.['data']?.['length']"
                    v-bind:pagedata="usePage<any>().props.user.followers">
                </Pagination>
            </div>
            <div v-show="ActiveCategory === 'Following'" class="my-2">
                <div v-if="!usePage<any>().props.user.following?.['data']?.['length']" class="mb-3 card card-body">
                    <div class="gap-3 text-center flex-container flex-dir-column">
                        <i class="text-5xl fad fa-face-sleeping text-muted"></i>
                        <div style="line-height: 16px">
                            <div class="text-xs fw-bold text-muted text-uppercase">
                                No following
                            </div>
                            <div class="text-xs text-muted fw-semibold">
                                {{ usePage<any>().props.user.username }} is not following anyone.
                            </div>
                        </div>
                    </div>
                </div>
                <div v-else class="grid-x grid-margin-x">
                    <div class="cell medium-6 mb-2"
                        v-for="(followingUser, index) in usePage<any>().props.user.following.data" :key="index">
                        <div class="card card-body">
                            <div class="grid-x grid-margin-x">

                                <div class="gap-2 cell small-7 medium-9 flex-container">
                                    <Link :href="route('user.profile', { username: followingUser.username })">
                                    <v-lazy-image :src="followingUser.avatar" width="45px"
                                        class="border img-fluid headshot rounded-circle border-secondary bg-dark"
                                        :alt="followingUser.username" />
                                    </Link>
                                    <div class="flex-wrap col-md-4 d-flex justify-content-center align-content-start"
                                        style="flex-direction: column">
                                        <div class="text-start">
                                            <Link :href="route('user.profile', { username: followingUser.username })"
                                                class="text-md">
                                            {{ followingUser.display_name }}
                                            </Link>
                                            <div></div>
                                            <Link :href="route('user.profile', { username: followingUser.username })"
                                                class="text-sm fw-semibold text-body">
                                            {{ '@' + followingUser.username }}
                                            </Link>
                                            <div></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-if="usePage<any>().props.user.following?.['data']?.['length']" class="mx-3 my-3 divider"></div>
                <Pagination v-if="usePage<any>().props.user.following?.['data']?.['length']"
                    v-bind:pagedata="usePage<any>().props.user.following">
                </Pagination>
            </div>
        </div>

        <div class="cell medium-3">
            <ul class="tabs flex-dir-column">
                <li v-for="(sectionItems, sectionName) in categorizedItems" class="tab-item" :key="sectionName">
                    <span class="text-xs fw-semibold text-muted text-uppercase">{{ sectionName }}</span>
                    <a  v-for="category in sectionItems" @click="setActiveCategory(category)"
                        class="tab-link squish" :class="{ active: category === ActiveCategory }" :key="category"> {{
                            capitalized(category) }}</a>
                </li>
            </ul>
        </div>
    </Sidebar>
    <Footer />
</template>
<script lang="ts">
export default {
    methods: {
        showModal(modalId: string): void {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.toggle("active");
            }
        },
    },

}
</script>
