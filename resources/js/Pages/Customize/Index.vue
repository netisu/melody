<script setup lang="ts">
import Navbar from "@/Components/LayoutParts/Navbar.vue";
import Sidebar from "@/Components/LayoutParts/Sidebar.vue";
import Footer from "@/Components/LayoutParts/Footer.vue";
import AppHead from "@/Components/AppHead.vue";
import { ref, onMounted, reactive, computed } from "vue";
import type { Ref } from "vue";

import axios from "axios"; // Import Axios
import { route } from "ziggy-js";
import JsonPagination from "@/Components/JsonPagination.vue";
import DummyError from "@/Images/dummy-error.png";
import DummyAvatar from "@/Images/dummy.png";

import { usePage } from "@inertiajs/vue3";
import VLazyImage from "v-lazy-image";

interface userAvatar {
    thumbnail: string;
    colors: {
        // This is the entire colors object as passed
        Head: string;
        Torso: string;
        LeftArm: string;
        RightArm: string;
        LeftLeg: string;
        RightLeg: string;
    };
    current_face_url: string; // The URL for the current face
}

interface AvatarColors {
    Head: string;
    Torso: string;
    LeftArm: string;
    RightArm: string;
    LeftLeg: string;
    RightLeg: string;
}

interface CategoryConfig {
    name: string;
    internal: string;
    icon: string;
}

interface CategoryGroup {
    icon: string;
    categories: CategoryConfig[];
}

const inventoryCategories = usePage<any>().props.categories as Record<string, CategoryGroup>;
const colors = usePage<any>().props.available_colors;
const currentcat = ref<string>("hat");
const CategoryItems = ref<{
    current_page: number;
    last_page: number;
    total: number;
    data: any[]; // Adjust the type of your data
} | null>(null);
const wearingItems = ref([]);
const wearingHats = ref([]);
const SelectedItemID = ref<Number>();
const slotValue = ref<Number>();
const selectHatSlot = ref(false);
const initialuserAvatar = usePage<any>().props.avatar as userAvatar;

const userAvatar = reactive({
    // --- Properties directly from Inertia Page Props ---
    colors: reactive<AvatarColors>({
        // Make 'colors' itself a reactive object
        Head: initialuserAvatar?.colors?.Head || "d3d3d3",
        Torso: initialuserAvatar?.colors?.Torso || "055e96",
        LeftArm: initialuserAvatar?.colors?.LeftArm || "d3d3d3",
        RightArm: initialuserAvatar?.colors?.RightArm || "d3d3d3",
        LeftLeg: initialuserAvatar?.colors?.LeftLeg || "d3d3d3",
        RightLeg: initialuserAvatar?.colors?.RightLeg || "d3d3d3",
    }),

    // Thumbnail URL from props
    image: computed<string>(
        () => initialuserAvatar?.thumbnail || "/assets/default_thumbnail.png"
    ),

    // Current face URL from props
    current_face: computed<string>(
        () => initialuserAvatar?.current_face_url || "/assets/default.png"
    ),
});

const selectedPart = ref<string | null>(null);
const selectedColor = ref<string | null>(null); // Initialize with a default color, e.g., white

const VrcRequest = ref(false); // Initialize as false
const imageRefreshKey = ref(0); // Initialize with 0

let thumbnail = "";

// Function to show a modal by toggling its "active" class
function showModal(modalId: string): void {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.toggle("active");
    }
}

const getItemList = async (category: Ref<string, string>, page: number) => {
    try {
        const response = await axios.get(
            route(`api.avatar.items`, { userId: usePage<any>().props.auth.user.id, category: category.value, page: page })
        );
        const data = response.data;
        CategoryItems.value = data;
    } catch (error) {
        console.error("Error fetching users:", error);
    }
};

const handlePageClick = (page: number) => {
    getItemList(currentcat, page);
};

// Mapping of internal part names to user-friendly names
const partNames = {
    head: "Head",
    LeftArm: "Left Arm",
    Torso: "Torso",
    RightArm: "Right Arm",
    LeftLeg: "Left Leg",
    RightLeg: "Right Leg",
};
// Function to submit the selected part's color to the API

function VRCReset() {
    VrcRequest.value = true;
    const requestData = {
        action: "reset",
    };
    axios
        .post(route(`avatar.update`), requestData)
        .then((response) => {
            // Handle the response from the server
            console.log(response.data);
            imageRefreshKey.value += 1;
            thumbnail = response.data;
            VrcRequest.value = false;
            getCurrentlyWearingItems();
        })
        .catch((error) => {
            VrcRequest.value = false;

            // Handle any errors
            console.error(error);
        });
    console.log(`Resetting`);
}

// Function to change the color of a body part
function changeColor(color: string, part: string) {
    if (!part) {
        console.error("Part is null or undefined");
        return Promise.reject("Part is null or undefined");
    } else {
        console.log(part);
    }

    VrcRequest.value = true;
    const requestData = {
        action: "color",
        body_part: part,
        color: color,
    };

    axios
        .post(route(`avatar.update`), requestData)
        .then((response) => {
            imageRefreshKey.value += 1;
            thumbnail = response.data;
            selectedColor.value = color;
            VrcRequest.value = false;
        })
        .catch((error) => {
            VrcRequest.value = false;
            console.error(error);
        });

    if (userAvatar.colors) {
        userAvatar.colors[part as keyof AvatarColors] = color;
    }

    console.log(`Changing ${part} color to: ${color}`);
}

// Function to select a body part
function selectPart(part: string): void {
    selectedPart.value = part;
}
function setColor(color: string): void {
    selectedColor.value = color;
}

function handlePartSelection(part: string): void {
    showModal("PartsModal");
    selectPart(part);
}
const SortItemByType = async (id: number, type: string, action: string) => {
    if (action === "wear") {
        if (type === "hat") {
            SelectedItemID.value = id;
            selectHatSlot.value = true;
        } else {
            WearItem(id, "none");
        }
    } else {
        if (type === "hat") {
            SelectedItemID.value = id;
            selectHatSlot.value = true;
        } else {
            TakeOffItem(id, "none");
        }
    }
};

const getItemsbyCategory = async (category) => {
    try {
        const response = await axios.get(
            route(`api.avatar.items`, { userId: usePage<any>().props.auth.user.id, category: category })
        );
        CategoryItems.value = response.data;
        currentcat.value = category;
    } catch (error) {
        console.error("Error fetching items:", error);
        return [];
    }
};

const getCurrentlyWearingItems = async () => {
    try {
        const response = await axios.get(
            route(`api.user.currently-wearing`, {
                id: usePage<any>().props.auth.user.id,
            })
        );
        wearingItems.value = response.data;
    } catch (error) {
        console.error("Error fetching currently wearing items:", error);
        return [];
    }
};

const getCurrentlyWearingHats = async () => {
    try {
        const response = await axios.get(route(`api.avatar.wearing-hats`));
        wearingHats.value = response.data;
    } catch (error) {
        console.error("Error fetching currently wearing items:", error);
        return [];
    }
};

const WearItem = async (id, slot) => {
    try {
        VrcRequest.value = true;
        const response = await axios.get(
            route(`api.avatar.wear-item`, { id: id, slot: slot })
        );
        imageRefreshKey.value += 1;
        thumbnail = response.data.thumbnail;
        VrcRequest.value = false;

        getCurrentlyWearingItems();
        if (slot === "none") {
            getCurrentlyWearingHats();
        }
    } catch (error) {
        VrcRequest.value = false;

        console.error("Error wearing item:", error);
        return [];
    }
};
const TakeOffItem = async (id, slot) => {
    try {
        VrcRequest.value = true;
        const response = await axios.get(
            route(`api.avatar.remove-item`, { id: id, slot: slot })
        );
        imageRefreshKey.value += 1;
        thumbnail = response.data.thumbnail;
        VrcRequest.value = false;

        getCurrentlyWearingItems();
    } catch (error) {
        VrcRequest.value = false;

        console.error("Error wearing item:", error);
        return [];
    }
};

function fillSlot(slotNumber) {
    console.log("Clicked on Hat Slot:", slotNumber);

    slotValue.value = slotNumber;
    WearItem(SelectedItemID.value, slotValue.value);
}

function onImgErrorSmall(id) {
    let source = document.getElementById(id) as HTMLImageElement;
    source.src = DummyError;
    source.onerror = null;

    return true;
}

onMounted(() => {
    getItemsbyCategory(currentcat.value),
        getCurrentlyWearingItems(),
        getCurrentlyWearingHats();
});
</script>
<style scoped>
#colorPicker {
    display: none;
    top: 0;
    left: 0;
    bottom: 0;
    right: 0;
    width: 100%;
    border: none;
    opacity: 0;
    position: absolute;
}

.fade-in-avatar {
    animation: fadeIn 0.5s;
    -webkit-animation: fadeIn 0.5s;
    -moz-animation: fadeIn 0.5s;
    -o-animation: fadeIn 0.5s;
    -ms-animation: fadeIn 0.5s;
}

@keyframes fadeIn {
    0% {
        opacity: 0;
    }

    100% {
        opacity: 1;
    }
}

@-moz-keyframes fadeIn {
    0% {
        opacity: 0;
    }

    100% {
        opacity: 1;
    }
}

@-webkit-keyframes fadeIn {
    0% {
        opacity: 0;
    }

    100% {
        opacity: 1;
    }
}

@-o-keyframes fadeIn {
    0% {
        opacity: 0;
    }

    100% {
        opacity: 1;
    }
}

@-ms-keyframes fadeIn {
    0% {
        opacity: 0;
    }

    100% {
        opacity: 1;
    }
}
</style>
<template>
    <AppHead pageTitle="Customize" description="Customize your charcter." :url="route('auth.login.page')" />
    <Navbar />
    <Sidebar>
        <div class="modal" id="PartsModal">
            <div class="modal-card modal-card-body modal-card-sm">
                <div class="section-borderless">
                    <div class="gap-2 align-middle flex-container align-justify">
                        <div v-if="selectedPart" class="text-lg fw-semibold">
                            Change {{ partNames[selectedPart] }} Color
                        </div>
                        <div v-else class="text-lg fw-semibold">
                            Select a part to change its color
                        </div>
                        <button @click="showModal('PartsModal')" class="btn-circle" data-toggle-modal="#PartsModal"
                            style="margin-right: -10px">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                <div class="mr-2 section-borderless grid-x">
                    <div class="mr-2" v-for="(color, index) in colors" :key="index">
                        <div class="ColorPickerItem" @click="setColor(color)" :class="{
                            active:
                                selectedColor === color ||
                                userAvatar[`color_${selectedPart}`] ===
                                color,
                        }" :style="{
                            backgroundColor: '#' + color,
                            display: 'inline-block',
                            width: '32px',
                            height: '32px',
                        }"></div>
                    </div>
                    <div class="color-picker">
                        <label for="colorPicker">
                            <div class="ColorPickerItem" type="color" style="
                                    background: linear-gradient(
                                        in hsl longer hue 45deg,
                                        red 0 100%
                                    );
                                    border-radius: 5px;
                                    background: url('/assets/img/rainbow-square.png');
                                    background-size: 100% 100%;
                                    display: inline-block;
                                    width: 32px;
                                    height: 32px;
                                "></div>
                        </label>
                        <input type="color" @change="
                            setColor(
                                (
                                    $event.target as HTMLInputElement
                                ).value.replace('#', '')
                            )
                            " id="colorPicker" />
                    </div>
                    <div class="text-xs text-muted fw-semibold">
                        After changing your avatar part your avatar will
                        rerender with the changes applied.
                    </div>
                    <div class="flex-wrap gap-2 flex-container justify-content-end section-borderless">
                        <button class="btn btn-secondary" @click="showModal('PartsModal')">
                            Cancel
                        </button>
                        <input v-if="selectedPart && !VrcRequest" @click="changeColor(selectedColor, selectedPart)"
                            type="submit" class="btn btn-success" value="Submit" />
                    </div>
                </div>
            </div>
        </div>
        <div class="cell medium-3">
            <div class="flex-container gap-2">
                <span class="text-xl fw-semibold">Avatar</span>
            </div>
            <div class="mb-3 card-body card card-item" style="position: relative">
                <div v-if="VrcRequest" id="avatar-loading" style="
                        width: auto;
                        height: auto;
                        position: absolute;
                        z-index: 1;
                        visibility: none;
                        box-sizing: border-box;

                        inset: 0px;
                        background-color: rgba(0, 0, 0, 0.58);
                        display: inline-grid;
                        justify-content: center;
                        align-items: center;
                    ">
                    <i class="justify-center text-3xl text-center align-middle fa-duotone fa-beat-fade fa-person-rays flex-container flex-dir-column text-info"
                        style="font-size: 3em"></i>
                </div>
                <div id="avatarDiv">
                    <v-lazy-image :src="thumbnail" class="fade-in-avatar" :src-placeholder="userAvatar.image"
                        style="display: block; margin: 0 auto !important" />
                </div>
                <div class="min-w-0 gap-2 mt-3 flex-container" style="z-index: -1">
                    <button @click="VRCReset()" class="btn btn-danger btn-sm text-truncate w-100">
                        <i class="fad fa-repeat"></i>
                    </button>
                    <button class="btn btn-info btn-sm text-truncate w-100">
                        <i class="fad fa-question"></i>
                    </button>
                </div>
            </div>
        </div>

        <div class="cell medium-9">
            <div class="flex-container gap-2">
                <div class="cell large-3">
                    <div class="text-xl fw-semibold">
                        {{
                            selectHatSlot ? "Pick a Hat Slot" : "Current Outfit"
                        }}
                    </div>
                    <a v-if="selectHatSlot" @click="selectHatSlot = !selectHatSlot" class="text-info text-sm"><i
                            class="fad fa-arrow-left"></i> Go back</a>
                </div>
            </div>
            <div class="mb-3">
                <template v-if="selectHatSlot === true" class="grid-x">
                    <div class="text-center cell medium-12">
                        <div class="grid-x grid-margin-x grid-padding-y">
                            <template v-for="n in 6" :key="n" :value="n">
                                <div class="cell large-3 medium-3 small-6" v-if="wearingHats[n - 1]">
                                    <div class="d-block">
                                        <div @click="
                                            SortItemByType(
                                                wearingHats[n - 1].id,
                                                wearingHats[n - 1]
                                                    .item_type,
                                                'wear'
                                            )
                                            " class="p-2 mb-1 card card-item position-relative">
                                            <img :src="wearingHats[n - 1].thumbnail
                                                " :id="wearingHats[n - 1].thumbnail
                                                    " @error="
                                                        onImgErrorSmall(
                                                            wearingHats[n - 1]
                                                                .thumbnail
                                                        )
                                                        " />
                                        </div>
                                        <Link as="p" style="cursor: pointer" :href="route(`store.item`, {
                                            id: wearingHats[n - 1].id,
                                        })
                                            " class="text-body text-center fw-semibold text-truncate">
                                        {{ wearingHats[n - 1].name }}
                                        </Link>
                                    </div>
                                </div>
                                <div class="cell large-3 medium-3 small-6" v-else>
                                    <div class="d-block">
                                        <div class="p-2 mb-1 card card-item position-relative d-flex align-items-center justify-content-center"
                                            style="
                                                min-height: 100px;
                                                cursor: pointer;
                                            " @click="fillSlot(n)">
                                            <i class="fa-solid fa-hat-beach text-2xl"></i>
                                            <div class="position-absolute bottom-0 start-0 w-100 text-center p-1" style="
                                                    background-color: rgba(
                                                        0,
                                                        0,
                                                        0,
                                                        0.05
                                                    );
                                                ">
                                                <p style="
                                                        margin-bottom: 0;
                                                        font-size: 0.8rem;
                                                        color: #555;
                                                    ">
                                                    Hat Slot {{ n }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>
                <template v-else>
                    <div class="card card-body mb-3">
                        <div class="grid-x">
                            <div class="cell medium-3 avatar-display-container">
                                <div class="avatar-wrapper">
                                    <div class="avatar-head-wrapper">
                                        <button class="avatar-body-part" id="Head" @click="
                                            handlePartSelection('Head')
                                            " :style="{ backgroundColor: '#' + userAvatar.colors.Head }">
                                            <VLazyImage :src="userAvatar.current_face" :src-placeholder="DummyAvatar"
                                                width="50" height="50" />
                                        </button>
                                    </div>
                                    <div class="avatar-Torso-arms-wrapper">
                                        <button class="avatar-body-part" id="LeftArm" @click="
                                            handlePartSelection('LeftArm')
                                            "
                                            :style="{ backgroundColor: '#' + userAvatar.colors.LeftArm }"></button>
                                        <button class="avatar-body-part" id="Torso" @click="
                                            handlePartSelection('Torso')
                                            " :style="{ backgroundColor: '#' + userAvatar.colors.Torso }"></button>
                                        <button class="avatar-body-part" id="RightArm" @click="
                                            handlePartSelection('RightArm')
                                            "
                                            :style="{ backgroundColor: '#' + userAvatar.colors.RightArm }"></button>
                                    </div>
                                    <div class="avatar-legs-wrapper">
                                        <button class="avatar-body-part" id="LeftLeg" @click="
                                            handlePartSelection('LeftLeg')
                                            "
                                            :style="{ backgroundColor: '#' + userAvatar.colors.LeftLeg }"></button>
                                        <button class="avatar-body-part" id="RightLeg" @click="
                                            handlePartSelection('RightLeg')
                                            "
                                            :style="{ backgroundColor: '#' + userAvatar.colors.RightLeg }"></button>
                                    </div>
                                </div>
                            </div>
                            <div class="cell medium-9 vertical-border-left">
                                <div v-if="ItemLoading || (wearingItems && wearingItems.length > 0)"
                                    class="grid-x grid-margin-x grid-padding-y">
                                    <div class="cell large-3 medium-3 small-6" v-for="(item, index) in wearingItems"
                                        :key="index">
                                        <div class="d-block">
                                            <div class="p-2 mb-1 card card-item position-relative">
                                                <div class="item-badges">
                                                    <div v-if="item.in_event"
                                                        class="mb-1 badge badge-warning fw-semibold">
                                                        <i class="fad fa-calendar-star" style="width: 18px"></i>Event
                                                    </div>
                                                    <div v-if="item.rare" class="mb-1 badge badge-info fw-semibold">
                                                        <i class="fad fa-comet" style="width: 18px"></i>Rare
                                                    </div>
                                                    <div v-if="item.sale_ongoing"
                                                        class="mb-1 badge badge-danger fw-semibold">
                                                        <i class="fad fa-badge-percent" style="width: 18px"></i>{{
                                                            item.percent_off + "%" }} off
                                                    </div>
                                                </div>
                                                <img @click="
                                                    SortItemByType(
                                                        item.id,
                                                        item.item_type,
                                                        'remove'
                                                    )
                                                    " :src="item.thumbnail" :id="item.thumbnail"
                                                    @error="onImgErrorSmall(item.thumbnail)" />
                                            </div>
                                            <Link :href="route(`store.item`, { id: item.id })"
                                                class="text-body fw-semibold text-truncate">
                                            {{ item.name }}
                                            </Link>
                                        </div>
                                        <div class="text-xs fw-semibold text-truncate">
                                            <span class="text-muted">By:</span>&nbsp;
                                            <Link :href="route(`user,profile`, { username: item.creator.username })">
                                            {{ "@" + item.creator.username
                                            }}<i class="fas fa-shield-check text-success ms-1"></i></Link>
                                        </div>
                                    </div>
                                </div>
                                <template v-else>
                                    <div class="gap-3 mb-2 text-center flex-container flex-dir-column">
                                        <i class="text-5xl fad fa-person-fairy text-muted"></i>
                                        <div style="line-height: 16px">
                                            <div class="text-xs fw-bold text-muted text-uppercase">
                                                No Items
                                            </div>
                                            <div class="text-muted fw-semibold">
                                                <p class="text-xs">
                                                    You are not wearing anything.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
        <div class="cell medium-3 mb-5">
            <div class="mb-2 text-xl fw-semibold">Your Inventory</div>
            <ul class="tabs flex-dir-column">
                <template v-for="(group, groupName) in inventoryCategories" :key="groupName">
                    <li class="tab-group-title">
                        <span class="text-xs fw-semibold text-muted text-uppercase">
                            <i :class="group.icon" class="me-1"></i>{{ groupName }}
                        </span>
                    </li>

                    <li class="tab-item" v-for="(subCategory, key) in group" :key="key">
                        <template v-if="key !== 'icon'">
                            <a href="#" class="tab-link squish" @click="getItemsbyCategory(subCategory.internal)"
                                :class="{ 'active': subCategory.internal === currentcat }">
                                <i class="me-1" :class="subCategory.icon"></i>
                                <span>{{ subCategory.name }}</span>
                            </a>
                        </template>
                    </li>
                </template>
            </ul>
        </div>
        <div class="cell medium-9">
            <div class="gap-3 text-center flex-container flex-dir-column">
                <div class="grid-x grid-margin-x">
                    <template v-if="CategoryItems && CategoryItems.data">
                        <div v-for="item in CategoryItems.data" class="cell large-2 medium-3 small-6">
                            <div class="d-block">
                                <div @click="
                                    SortItemByType(
                                        item.id,
                                        item.item_type,
                                        'wear'
                                    )
                                    " class="p-2 mb-1 card card-inner position-relative">
                                    <img :src="item.thumbnail" />
                                </div>
                                <Link as="p" style="cursor: pointer" :href="route(`store.item`, { id: item.id })
                                    " class="text-body fw-semibold text-truncate">
                                {{ item.name }}
                                </Link>
                            </div>
                        </div>
                    </template>
                </div>
                <JsonPagination v-if="CategoryItems && CategoryItems.data.length" @page-clicked="handlePageClick"
                    :pagedata="CategoryItems" />
                <div v-if="!CategoryItems || !CategoryItems.data.length"
                    class="gap-3 text-center flex-container flex-dir-column">
                    <i class="text-5xl fad fa-person-fairy text-info"></i>
                    <div style="line-height: 16px">
                        <div class="text-xs fw-bold text-info text-uppercase">
                            No Items
                        </div>
                        <div class="text-xs text-info fw-semibold">
                            You have no {{ currentcat.replace(/_/g, " ")
                            }}{{ currentcat !== "pants" ? "s" : null }}.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </Sidebar>
    <Footer />
    <!--
    {{ AVATAR CLASSES }}
    Body Colors:
    {{ userAvatar.color_Head }}
    {{ userAvatar.color_Torso }}
    {{ userAvatar.color_LeftArm }}
    {{ userAvatar.color_RightArm }}
    {{ userAvatar.color_LeftLeg }}
    {{ userAvatar.color_RightLeg }}

    Items: [
        Hats [
    {{ userAvatar.items[0].hats.hat_1 ?? 'none' }}
    {{ userAvatar.items[0].hats.hat_2 ?? 'none' }}
    {{ userAvatar.items[0].hats.hat_3 ?? 'none' }}
    {{ userAvatar.items[0].hats.hat_4 ?? 'none' }}
    {{ userAvatar.items[0].hats.hat_5 ?? 'none' }}
    {{ userAvatar.items[0].hats.hat_6 ?? 'none' }}
],
    {{ userAvatar.items[0].face ?? 'none' }}
    {{ userAvatar.items[0].gear ?? 'none' }}
    {{ userAvatar.items[0].shirt ?? 'none' }}
    {{ userAvatar.items[0].pants ?? 'none' }}

    ];
!-->
</template>
