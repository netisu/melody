<script setup lang="ts">
import Sidebar from '@/Components/LayoutParts/Sidebar.vue';
import Navbar from '@/Components/LayoutParts/Navbar.vue';
import Footer from '@/Components/LayoutParts/Footer.vue';
import AppHead from '@/Components/AppHead.vue';
import { usePage } from '@inertiajs/vue3';
import { route } from "ziggy-js";
import axios from 'axios';
import { ref } from 'vue';
import throttle from 'lodash/throttle';


defineProps<{
    item?: { type: Object, default: null },
    recommendations?: Array<any>,
}>();

const JSONDATA = ref<{ message: string; type: string } | null>(null);
const thumbnail = ref<HTMLImageElement>(null);

let isPreview = ref(false);

const itemOwnership = ref(usePage<any>().props.itemOwnership);

function capitalizeFirstLetter(str) {
    return str.charAt(0).toUpperCase() + str.slice(1);
}

function showModal(modalId: string): void {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.toggle("active");
    }
}

const purchaseItem = (currencyType) => {
    const modalId = `purchase-with-${currencyType}-modal`;
    showModal(modalId);

    axios.get(`/sanctum/csrf-cookie`).then(() => {
        axios.post(route(`api.store.purchase`, { id: usePage<any>().props.item.id, currencyType: currencyType }))
            .then(response => {
                const { message, type } = response.data;
                if (type === 'success') {
                    itemOwnership.value = true;
                }
                JSONDATA.value = { message, type };
            })
            .catch(error => {
                console.error(error);
            });
    });
};

const reRender = throttle(() => {
    axios.get(`/sanctum/csrf-cookie`).then(() => {
        axios.post(route(`api.item.rerender`, { id: usePage<any>().props.item.id }))
            .then(response => {
                JSONDATA.value = response.data;
            })
            .catch(error => {
                console.error(error);
            });
    });
}, 5000);

const swap = () => {
    if (isPreview.value) {
        isPreview.value = false;
    } else {
        isPreview.value = true;
    }

    const thumbnail = document.getElementById("thumbnail") as HTMLImageElement;
    thumbnail.src = isPreview.value
        ? usePage<any>().props.item.preview
        : usePage<any>().props.item.thumbnail;
};

function onImgErrorSmall(id) {
    let source = document.getElementById(id) as HTMLImageElement;
    source.src = "/assets/img/dummy-error.png";
    source.onerror = null;

    return true;
}
</script>
<template>
    <AppHead :pageTitle="usePage<any>().props.item.name" :description="usePage<any>().props.item.description"
        :url="route(`store.item`, { id: usePage<any>().props.item.id })" :item="true"
        :iid="usePage<any>().props.item.id" :itime="usePage<any>().props.item.time_off_sale"
        :cover="usePage<any>().props.item.thumbnail" />
    <Navbar />
    <Sidebar :JSONALERT="JSONDATA">
        <div class="modal" id="purchase-with-Stars-modal">
            <div class="modal-card modal-card-body modal-card-sm">
                <div class="section-borderless">
                    <div class="gap-2 align-middle flex-container align-justify">
                        <div class="text-lg fw-semibold">Confirm Purchase</div>
                        <button class="btn-circle" @click="showModal('purchase-with-Stars-modal')"
                            data-toggle-modal="#purchase-with-Stars-modal" style="margin-right: -10px">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                <div class="section-borderless">

                    <div class="text-sm text-muted fw-semibold">
                        Are you sure you want to buy
                        <span class="text-body fw-semibold">{{ usePage<any>().props.item.name }}</span>
                        for
                        <span class="text-success">
                            <i class="fas fa-stars me-1"></i>
                            {{ usePage<any>().props.item.cost_Stars }}
                                Stars
                        </span>?
                    </div>

                </div>
                <div class="gap-2 flex-container align-right section-borderless">
                    <form @submit.prevent="purchaseItem('Stars')">
                        <button type="submit" class="btn btn-success btn-sm">Buy Now</button>
                        <button type="button" class="btn btn-secondary btn-sm"
                            @click="showModal('purchase-with-Stars-modal')"
                            data-toggle-modal="#purchase-with-Stars-modal">
                            Cancel
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <div class="modal" id="purchase-with-sparkles-modal">
            <div class="modal-card modal-card-body modal-card-sm">
                <div class="section-borderless">
                    <div class="gap-2 align-middle flex-container align-justify">
                        <div class="text-lg fw-semibold">Confirm Purchase</div>
                        <button class="btn-circle" @click="showModal('purchase-with-sparkles-modal')"
                            data-toggle-modal="#purchase-with-sparkles-modal" style="margin-right: -10px">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                <div class="section-borderless">
                    <div class="text-sm text-muted fw-semibold">
                        Are you sure you want to buy
                        <span class="text-body fw-semibold">{{ usePage<any>().props.item.name }}</span>
                        for
                        <span class="text-warning">
                            <i class="fas fa-sparkles me-1"></i>
                            {{ usePage<any>().props.item.cost_sparkles }}
                                sparkles
                        </span>?
                    </div>
                </div>
                <div class="gap-2 flex-container align-right section-borderless">
                    <form @submit.prevent="purchaseItem('sparkles')">
                        <button type="submit" class="btn btn-warning btn-sm">Buy Now</button>
                        <button class="btn btn-secondary btn-sm" type="button"
                            @click="showModal('purchase-with-sparkles-modal')"
                            data-toggle-modal="#purchase-with-sparkles-modal">
                            Cancel
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <div class="cell large-12">
            <div v-if="!usePage<any>().props.item.public_view" class="alert alert-danger mb-3">
                <i class="fad fa-shield"></i> This item is hidden, only you and staff can see it.
            </div>
            <div v-if="usePage<any>().props.item.status === 'pending'" class="alert alert-danger">
                <i class="fad fa-clock"></i> This item is pending approval.
            </div>
            <div class="grid-x grid-margin-x grid-padding-y">
                <div class="cell large-4">
                    <div class="mb-3 card card-item overflow-hidden" :class="{ 'card-item-owned': itemOwnership }">
                        <div class="p-4 position-relative">
                            <div style="
                                position: absolute;
                                top: 10px;
                                left: 10px;
                            ">
                                <div v-if="usePage<any>().props.item.rare" class="mb-1">
                                    <span class="badge badge-witch fw-semibold">
                                        <i class="fas  fa-stars" style="width: 18px"></i>Showpiece
                                    </span>
                                </div>
                                <div v-if="isNewItem(usePage<any>().props.item.created_at)" class="mb-1">
                                    <span class="badge badge-info fw-semibold">
                                        <i class="fas fa-fire" style="width: 18px"></i>New
                                    </span>
                                </div>
                                <div class="mb-1">
                                    <div v-if="usePage<any>().props.item.sale_ongoing"
                                        class="mb-1 badge badge-danger fw-semibold">
                                        <i class="fas fa-badge-percent" style="width: 18px"></i>{{ usePage<any>
                                            ().props.item.percent_off + '%' }} off
                                    </div>
                                </div>
                                <!--div class="mb-1">
                                    <span class="badge badge-danger fw-semibold">
                                        <i class="fas fa-clock" style="width: 18px"></i>Goes offsale in 10:10:12
                                    </span>
                                </div-->
                            </div>
                            <div class="gap-1 flex-container flex-dir-column" style="
                                position: absolute;
                                bottom: 10px;
                                right: 10px;
                            ">
                                <div class="ms-auto"
                                    v-if="usePage<any>().props.auth.user && usePage<any>().props.auth.user.pendingAssets != null">
                                    <button class="btn btn-danger btn-xs"
                                        @click="reRender(usePage<any>().props.item.id)">
                                        <i class="fad fa-rotate"></i> Rerender
                                    </button>
                                </div>
                                <div class="ms-auto">
                                    <button v-if="usePage<any>().props.item.preview" class="btn btn-secondary btn-xs"
                                        @click="swap()">
                                        <i class="fad fa-shirt"></i> {{ isPreview ? 'View Item' : 'View Preview' }}
                                    </button>
                                </div>
                                <div class="ms-auto" v-if="usePage<any>().props.item.type == 'crate'">
                                    <button class="btn btn-success btn-xs" data-toggle-modal="#view-crate-content-modal"
                                        @click="showModal('view-crate-content-modal')">
                                        View Contents
                                    </button>
                                </div>
                            </div>
                            <img :src="usePage<any>().props.item.thumbnail" @error="onImgErrorSmall('thumbnail')"
                                class="mx-auto d-block" id="thumbnail" ref="thumbnail" width="512" height="512">
                        </div>
                        <div v-if="itemOwnership"
                            class="gap-2 py-2 overflow-hidden text-sm text-center align-middle flex-container align-center bg-success fw-semibold">
                            <i class="text-lg fad fa-party-horn"></i>
                            Yahoo! You own this item.
                        </div>
                    </div>
                    <div class="gap-3 mb-3 align-middle flex-container"
                        v-if="usePage<any>().props.item.type == 'crate'">
                        <button class="btn btn-info btn-xs w-100" data-toggle-modal="#crate-roll-modal"
                            @click="showModal('crate-roll-modal')">
                            Open Crate
                        </button>
                        <div class="text-xs text-center flex-child-grow text-danger fw-bold text-uppercase">
                            9 Owned
                        </div>
                        <button class="text-muted" data-toggle-modal="#crate-info-modal"
                            @click="showModal('crate-info-modal')">
                            <i class="fad fa-question-circle"></i>
                        </button>
                    </div>

                    <div v-if="usePage<any>().props.item.rare">
                        <div class="mb-1 text-xl fw-semibold">
                            Private Sellers
                            <span class="text-xs fw-semibold text-muted">This item has 3 listings</span>
                        </div>
                        <div class="card card-body">
                            <div class="gap-4 align-middle section flex-container">
                                <div class="gap-2 align-middle flex-container flex-child-grow">
                                    <img src="/assets/img/dummy_headshot.png" class="headshot" width="50">
                                    <div style="line-height: 10px">
                                        <div class="text-lg fw-semibold">
                                            Nabrious
                                        </div>
                                        <div class="text-xs fw-semibold text-muted">
                                            Copy #1 of 200
                                        </div>
                                    </div>
                                </div>
                                <button class="btn btn-success btn-xs w-100"
                                    data-toggle-modal="#purchase-with-Stars-modal">
                                    <i class="fas fa-stars" style="width: 30px"></i>10 Stars
                                </button>
                            </div>
                            <div class="gap-4 align-middle section flex-container">
                                <div class="gap-2 align-middle flex-container flex-child-grow">
                                    <img src="/assets/img/dummy_headshot.png" class="headshot" width="50">
                                    <div style="line-height: 10px">
                                        <div class="text-lg fw-semibold">
                                            Nabrious
                                        </div>
                                        <div class="text-xs fw-semibold text-muted">
                                            Copy #1 of 200
                                        </div>
                                    </div>
                                </div>
                                <button class="btn btn-success btn-xs w-100"
                                    data-toggle-modal="#purchase-with-Stars-modal">
                                    <i class="fas fa-stars" style="width: 30px"></i>10 Stars
                                </button>
                            </div>
                            <div class="gap-4 align-middle section flex-container">
                                <div class="gap-2 align-middle flex-container flex-child-grow">
                                    <img src="/assets/img/dummy_headshot.png" class="headshot" width="50">
                                    <div style="line-height: 10px">
                                        <div class="text-lg fw-semibold">
                                            Nabrious
                                        </div>
                                        <div class="text-xs fw-semibold text-muted">
                                            Copy #1 of 200
                                        </div>
                                    </div>
                                </div>
                                <button class="btn btn-success btn-xs w-100"
                                    data-toggle-modal="#purchase-with-Stars-modal">
                                    <i class="fas fa-stars" style="width: 30px"></i>10 Stars
                                </button>
                            </div>
                            <div class="gap-4 align-middle section flex-container">
                                <div class="gap-2 align-middle flex-container flex-child-grow">
                                    <img src="/assets/img/dummy_headshot.png" class="headshot" width="50">
                                    <div style="line-height: 10px">
                                        <div class="text-lg fw-semibold">
                                            Nabrious
                                        </div>
                                        <div class="text-xs fw-semibold text-muted">
                                            Copy #1 of 200
                                        </div>
                                    </div>
                                </div>
                                <button class="btn btn-success btn-xs w-100"
                                    data-toggle-modal="#purchase-with-Stars-modal">
                                    <i class="fas fa-stars" style="width: 30px"></i>10 Stars
                                </button>
                            </div>
                            <ul class="section pagination flex-container align-center">
                                <button class="page-link page-disabled">
                                    <i class="fa-regular fa-chevron-left"></i>
                                </button>
                                <button class="page-link page-active">
                                    1
                                </button>
                                <button class="page-link">2</button>
                                <button class="page-link">3</button>
                                <button class="page-link">
                                    <i class="fa-regular fa-chevron-right"></i>
                                </button>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="cell large-7">
                    <div class="card card-body">
                        <div class="gap-2 mb-2 align-middle flex-container align-justify">
                            <div class="text-3xl fw-semibold">
                                {{ usePage<any>().props.item.name }}
                            </div>
                            <div class="position-relative" style="margin-right: -10px">
                                <Link
                                    v-if="usePage<any>().props.auth.user && usePage<any>().props.auth.user.id == usePage<any>().props.item.creator.id"
                                    as="button" :href="route(`store.edit`, { id: usePage<any>().props.item.id })"
                                    class="btn-circle" data-tooltip-title="More" data-tooltip-placement="bottom">
                                <i class="fad fa-pen"></i>
                                </Link>
                            </div>
                        </div>
                        <div class="gap-2 mb-3 align-middle flex-container fw-semibold">
                            <Link
                                :href="route('user.profile', { username: usePage<any>().props.item.creator.username })"
                                class="gap-2 align-middle flex-container"
                                :class="{ 'text-danger': usePage<any>().props.item.creator.staff }">
                            <img :src="usePage<any>().props.item.creator.thumbnail" class="headshot" width="38">
                            <div style="line-height: 17px">
                                <div>{{ usePage<any>().props.item.creator.displayname }}</div>
                                <div class="text-xs text-muted text-truncate" style="max-width: 140px">
                                    {{ '@' + usePage<any>().props.item.creator.username }}
                                </div>
                            </div>
                            </Link>
                        </div>
                        <div class="mb-1 text-xs fw-bold text-muted">

                            <span class="text-uppercase"
                                v-if="usePage<any>().props.auth.user && itemOwnership != true && usePage<any>().props.item.onsale">
                                Purchase With
                            </span>
                            <span class="text-md text-muted" v-else-if="!usePage<any>().props.item.onsale">
                                This item is offsale.
                            </span>
                            <span class="text-md text-info" v-else-if="itemOwnership">
                                You already own this item.
                            </span>
                            <Link :href="route('auth.login.page')" class="text-md text-warning squish" as="span" v-else>
                            Login to Purchase {{ usePage<any>().props.item.name }}
                                </Link>
                        </div>
                        <div class="gap-2 align-middle flex-container-lg"
                            v-if="usePage<any>().props.auth.user && itemOwnership != true && usePage<any>().props.item.onsale">
                            <button class="mb-2 btn btn-success btn-sm w-100"
                                data-toggle-modal="#purchase-with-Stars-modal"
                                @click="showModal('purchase-with-Stars-modal')">
                                <i class="fas fa-stars" style="width: 34px"></i>{{ usePage<any>
                                    ().props.item.cost_Stars }} Stars
                            </button>
                            <div class="mb-2 text-xs fw-bold text-uppercase text-muted">
                                or
                            </div>
                            <button class="mb-2 btn btn-warning btn-sm w-100"
                                data-toggle-modal="#purchase-with-sparkles-modal"
                                @click="showModal('purchase-with-sparkles-modal')">
                                <i class="fas fa-sparkles" style="width: 34px"></i>{{ usePage<any>().props.item.cost_sparkles
                                }}
                                    sparkles
                            </button>
                        </div>
                    </div>
                    <div v-if="usePage<any>().props.item.rare">
                        <div class="mb-1 text-xl fw-semibold">
                            Price Chart
                        </div>
                        <div class="card card-body">
                            <div class="text-xs fw-bold text-uppercase text-muted">
                                PRICE CHART GOES HERE
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="cell large-12 mb-3">
                <div class="grid-x grid-margin-x grid-align-center grid-padding-y">
                    <div class="mb-2 cell small-3">
                        <div class="text-xs fw-bold text-uppercase text-muted text-truncate"> Date Created </div>
                        <div class="fw-semibold text-truncate">{{ usePage<any>().props.item.DateHum }}</div>
                    </div>
                    <div class="mb-2 cell small-3">
                        <div class="text-xs fw-bold text-uppercase text-muted text-truncate"> Last Updated </div>
                        <div class="fw-semibold text-truncate">  {{ usePage<any>().props.item.UpdateHum }}
                        </div>
                    </div>
                    <div class="mb-2 cell small-3 mb-md-0">
                        <div class="text-xs fw-bold text-uppercase text-muted text-truncate"> Type </div>
                        <div class="fw-semibold text-capitalize text-truncate">                                    {{ capitalizeFirstLetter(usePage<any>().props.item.item_type) }}
                        </div>
                    </div>
                    <div class="cell small-3">
                        <div class="text-xs fw-bold text-uppercase text-muted text-truncate"> Owners </div>
                        <div class="fw-semibold text-truncate">                                    {{ usePage<any>().props.item.owners.count }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="mb-1 text-xl fw-semibold">
                Description
            </div>
            <div class="divider mx-1 my-2" />
            <div class="mb-3 text-md">
                {{ usePage<any>().props.item.description ? usePage<any>().props.item.description : 'This item does not have a description.' }}
            </div>
            <div class="mb-1 text-xl fw-semibold">
                Items by {{ usePage<any>().props.item.creator.username }}
            </div>
            <div class="divider mx-1 my-2" />
            <div class="text-sm text-muted mt-2">
                <div class="mb-2 text-sm text-body fw-semibold">
                    <div v-if="recommendations.length" class="grid-x grid-margin-x">
                        <div class="cell large-2 medium-3 small-6" v-for="suggestion in recommendations">
                            <Link :href="route('store.item', { id: suggestion.id })" class="d-block">
                            <div class="p-2 mb-1 card card-inner position-relative">
                                <img :src="suggestion.thumbnail">
                            </div>
                            <div class="text-body text-md fw-semibold text-truncate">
                                {{ suggestion.name }}
                            </div>
                            </Link>
                        </div>
                    </div>
                    <div v-else class="text-sm fw-bold text-muted">
                        There is no other items from this creator
                    </div>
                </div>
            </div>
        </div>
    </Sidebar>
    <Footer />
</template>

<script lang="ts">
export default {
    name: 'MyComponent',
    methods: {
        isNewItem(created_at) {
            const currentTime = new Date() as any;
            const itemTime = new Date(created_at) as any;
            const timeDifference = currentTime - itemTime;
            const twoHoursInMillis = 2 * 60 * 60 * 1000; // 2 hours in milliseconds

            return timeDifference < twoHoursInMillis;
        }
    },
};
</script>
