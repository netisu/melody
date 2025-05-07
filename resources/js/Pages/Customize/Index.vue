<script setup lang="ts">
import Navbar from "@/Components/LayoutParts/Navbar.vue";
import Sidebar from "@/Components/LayoutParts/Sidebar.vue";
import Footer from "@/Components/LayoutParts/Footer.vue";
import AppHead from '@/Components/AppHead.vue';
import { ref, onMounted, reactive, computed } from "vue";
import type { Ref } from "vue";

import axios from "axios"; // Import Axios
import { route } from "momentum-trail"
import JsonPagination from '@/Components/JsonPagination.vue';

import { usePage } from "@inertiajs/vue3";
import VLazyImage from "v-lazy-image";

const colors = usePage<any>().props.colors;
const currentcat = ref("hat");
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

var userAvatar = reactive({
    color_head: computed<any>(
        () => (usePage<any>().props.avatar as Record<string, unknown>)?.["color_head"]
    ),
    color_torso: computed<any>(
        () => (usePage<any>().props.avatar as Record<string, unknown>)?.["color_torso"]
    ),
    color_left_arm: computed<any>(
        () => (usePage<any>().props.avatar as Record<string, unknown>)?.["color_left_arm"]
    ),
    color_right_arm: computed<any>(
        () => (usePage<any>().props.avatar as Record<string, unknown>)?.["color_right_arm"]
    ),
    color_left_leg: computed<any>(
        () => (usePage<any>().props.avatar as Record<string, unknown>)?.["color_left_leg"]
    ),
    color_right_leg: computed<any>(
        () => (usePage<any>().props.avatar as Record<string, unknown>)?.["color_right_leg"]
    ),
    image: computed<any>(
        () => (usePage<any>().props.avatar as Record<string, unknown>)?.["thumbnail"]
    ),
    current_face: computed<any>(
        () => (usePage<any>().props.avatar as Record<string, unknown>)?.["current_face"]
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
        const response = await axios.get(route(`api.avatar.items`, { category: category, page: page }));
        const data = response.data;
        CategoryItems.value = data;
    } catch (error) {
        console.error('Error fetching users:', error);
    }
};

const handlePageClick = (page: number) => {
    getItemList(currentcat.value, page);
};

// Mapping of internal part names to user-friendly names
const partNames = {
    head: "Head",
    left_arm: "Left Arm",
    torso: "Torso",
    right_arm: "Right Arm",
    left_leg: "Left Leg",
    right_leg: "Right Leg",
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
            // Handle the response from the server
            imageRefreshKey.value += 1;
            thumbnail = response.data;
            selectedColor.value = color;
            VrcRequest.value = false;
        })
        .catch((error) => {
            VrcRequest.value = false;
            // Handle any errors
            console.error(error);
        });

    const updatedUserAvatar = { ...userAvatar };
    if (part === "head") {
        updatedUserAvatar.color_head = color;
    } else if (part === "left_arm") {
        updatedUserAvatar.color_left_arm = color;
    } else if (part === "torso") {
        updatedUserAvatar.color_torso = color;
    } else if (part === "right_arm") {
        updatedUserAvatar.color_right_arm = color;
    } else if (part === "left_leg") {
        updatedUserAvatar.color_left_leg = color;
    } else {
        updatedUserAvatar.color_right_leg = color;
    }
    userAvatar = updatedUserAvatar;

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
        const response = await axios.get(route(`api.avatar.items`, { category: category }));
        CategoryItems.value = response.data;
        currentcat.value = category;
    } catch (error) {
        console.error("Error fetching items:", error);
        return [];
    }
};

const getCurrentlyWearingItems = async () => {
    try {
        const response = await axios.get(route(`api.user.currently-wearing`, { id: usePage<any>().props.auth.user.id }));
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
    console.log('Clicked on Hat Slot:', slotNumber);

    slotValue.value = slotNumber;
    WearItem(SelectedItemID.value, slotValue.value)
}

function onImgErrorSmall(id) {
    let source = document.getElementById(id) as HTMLImageElement;
    source.src = "/assets/img/dummy-error.png";
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
                                userAvatar[`color_${selectedPart}`] === color,
                        }" :style="{
                            backgroundColor: '#' + color,
                            display: 'inline-block',
                            width: '32px',
                            height: '32px',
                        }"></div>
                    </div>
                    <div class="color-picker">
                        <label for="colorPicker">
                            <div class="ColorPickerItem" type="color" style="background: linear-gradient(in hsl longer hue 45deg, red 0 100%);border-radius: 5px;background: url('/assets/img/rainbow-square.png');background-size: 100% 100%;display: inline-block;
                            width: 32px;
                            height: 32px;"
                        ></div>
                        </label>
                        <input type="color" @change="setColor(($event.target as HTMLInputElement).value.replace('#', ''))" id="colorPicker">
                    </div>
                    <div class="text-xs text-muted fw-semibold">
                        After changing your avatar part your avatar will rerender with the changes
                        applied.
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
            <div class=" flex-container gap-2">
                <span class="text-xl fw-semibold">Avatar</span>
            </div>
            <div class="mb-3 card-body card card-item" style="position: relative;">
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
                        style="
                font-size: 3em;
              "></i>
                </div>
                <div id="avatarDiv">
                    <v-lazy-image :src="thumbnail" class="fade-in-avatar" :src-placeholder="userAvatar.image"
                        style="display: block; margin: 0 auto !important" />

                </div>
                <div class="min-w-0 gap-2 mt-3 flex-container" style="z-index: -1;">
                    <button @click="VRCReset()" class="btn btn-danger btn-sm text-truncate w-100">
                        <i class="fad fa-repeat"></i>
                    </button>
                    <button class="btn btn-info btn-sm text-truncate w-100"> <i class="fad fa-question"></i>
                    </button>
                </div>
            </div>

        </div>

        <div class="cell medium-9">
            <div class=" flex-container gap-2">
                <span class="text-xl fw-semibold">{{ selectHatSlot ? 'Pick a Hat Slot' : 'Current Outfit' }}</span>
            </div>
            <div class="mb-3">
                <div class="grid-x">
                    <template v-if="selectHatSlot === true" class="text-center cell medium-12">
                        <div class="grid-x grid-margin-x grid-padding-y">
                            <template v-for="n in 6" :key="n" :value="n">
                                <div class="cell large-3 medium-3 small-6" v-if="wearingHats[n - 1]">
                                    <div class="d-block">
                                        <div @click="SortItemByType(wearingHats[n - 1].id, wearingHats[n - 1].item_type, 'wear')"
                                            class="p-2 mb-1 card card-item position-relative">
                                            <img :src="wearingHats[n - 1].thumbnail" :id="wearingHats[n - 1].thumbnail"
                                                @error="onImgErrorSmall(wearingHats[n - 1].thumbnail)" />
                                        </div>
                                        <Link as="p" style="cursor:pointer;"
                                            :href="route(`store.item`, { id: wearingHats[n - 1].id })"
                                            class="text-body text-center fw-semibold text-truncate">
                                        {{ wearingHats[n - 1].name }}
                                        </Link>
                                    </div>
                                </div>
                                <div class="cell large-3 medium-3 small-6" v-else>
                                    <div class="d-block">
                                        <div class="p-2 mb-1 card card-item position-relative d-flex align-items-center justify-content-center"
                                            style="min-height: 100px; cursor: pointer;" @click="fillSlot(n)">
                                            <i class="fa-solid fa-hat-beach text-2xl"></i>
                                            <div class="position-absolute bottom-0 start-0 w-100 text-center p-1"
                                                style="background-color: rgba(0, 0, 0, 0.05);">
                                                <p style="margin-bottom: 0; font-size: 0.8rem; color: #555;">Hat
                                                    Slot {{ n }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>
                    <template v-else>
                        <div class="text-center cell medium-3 align-left">
                            <div class="text-center flex-container align-center">
                                <div class="text-center" style="transform:scale(0.7);margin-top:-25px;">
                                    <div style="margin-bottom: 5px">
                                        <button class="avatar-body-part" @click="handlePartSelection('head')" id="head"
                                            :style="{
                                                backgroundColor: '#' + userAvatar.color_head,
                                                padding: '5px',
                                                borderRadius: '15px',
                                                marginTop: '-1px',
                                                width: '60', height: '50',
                                            }">
                                            <VLazyImage :src="userAvatar.current_face"
                                                :src-placeholder="usePage<any>().props.site.production.domains.storage + '/assets/default.png'"
                                                width="50" height="50" />
                                        </button>
                                    </div>
                                    <div style="display: flex; margin-bottom: 5px">
                                        <button class="avatar-body-part" @click="handlePartSelection('left_arm')"
                                            id="left_arm" :style="{
                                                backgroundColor: '#' + userAvatar.color_left_arm,
                                                padding: '50px',
                                                paddingRight: '0px',
                                            }"></button>

                                        <button class="avatar-body-part" @click="handlePartSelection('torso')"
                                            id="torso" :style="{
                                                backgroundColor: '#' + userAvatar.color_torso,
                                                padding: '50px',
                                            }"></button>

                                        <button class="avatar-body-part" @click="handlePartSelection('right_arm')"
                                            id="right_arm" :style="{
                                                backgroundColor: '#' + userAvatar.color_right_arm,
                                                padding: '50px',
                                                paddingRight: '0px',
                                            }"></button>
                                    </div>
                                    <div class="display: flex; margin-bottom: 5px">
                                        <button class="avatar-body-part" @click="handlePartSelection('left_leg')"
                                            name="left_leg" :style="{
                                                backgroundColor: '#' + userAvatar.color_left_leg,
                                                padding: '50px',
                                                paddingRight: '0px',
                                                paddingLeft: '47px',
                                            }"></button>

                                        <button class="avatar-body-part" @click="handlePartSelection('right_leg')"
                                            name="right_leg" :style="{
                                                backgroundColor: '#' + userAvatar.color_right_leg,
                                                padding: '50px',
                                                paddingRight: '0px',
                                                borderBottom: '15px',
                                                paddingLeft: '47px',
                                            }"></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="text-center cell medium-9">

                            <div class="grid-x grid-margin-x grid-padding-y">
                                <div class="cell large-3 medium-3 small-6" v-for="(item, index) in wearingItems"
                                    :key="index">
                                    <div class="d-block" @click="SortItemByType(item.id, item.item_type, 'remove')">
                                        <div class="p-2 mb-1 card card-item position-relative">
                                            <img :src="item.thumbnail" :id="item.thumbnail"
                                                @error="onImgErrorSmall(item.thumbnail)" />
                                        </div>
                                        <Link as="p" style="cursor:pointer;"
                                            :href="route(`store.item`, { id: item.id })"
                                            class="text-body text-center fw-semibold text-truncate">
                                        {{ item.name }}
                                        </Link>
                                    </div>
                                </div>
                            </div>

                            <div v-if="!wearingItems.length" class="gap-3 text-start flex-container flex-dir-column">
                                <div class="text-md text-muted fw-semibold">
                                    You are not wearing any items.
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
        <div class="mb-3 card-body  cell medium-12">
            <div class="mb-2 align-middle grid-x">
                <div class="cell large-9">
                    <div class="mb-2 text-xl fw-semibold">Inventory</div>
                </div>
                <div class="cell large-3">
                    <div class="gap-2 align-middle flex-container-lg">
                        <select @change="getItemsbyCategory(($event.target as HTMLInputElement).value)"
                            class="mb-2 form form-xs form-select form-has-section-color">
                            <option v-for="category in usePage<any>().props.categories" :key="category.internal"
                                :value="category.internal" :active="currentcat === category.internal">
                                {{ category.name }}
                            </option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="section">
                <div class="gap-3 text-center flex-container flex-dir-column">
                    <div class="grid-x grid-margin-x">
                        <template v-if="CategoryItems && CategoryItems.data">
                            <div v-for="item in CategoryItems.data" class="cell large-2 medium-3 small-6">
                                <div class="d-block">
                                    <div @click="SortItemByType(item.id, item.item_type, 'wear')"
                                        class="p-2 mb-1 card card-inner position-relative">
                                        <img :src="item.thumbnail" />
                                    </div>
                                    <Link as="p" style="cursor:pointer;" :href="route(`store.item`, { id: item.id })"
                                        class="text-body fw-semibold text-truncate">
                                    {{ item.name }}
                                    </Link>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
                <JsonPagination v-if="CategoryItems && CategoryItems.data" @page-clicked="handlePageClick"
                    :pagedata="CategoryItems" />
                <div v-if="!CategoryItems || !CategoryItems.data.length"
                    class="gap-3 text-center flex-container flex-dir-column">
                    <i class="text-5xl fad fa-crate-apple text-muted"></i>
                    <div style="line-height: 16px">
                        <div class="text-xs fw-bold text-muted text-uppercase">No Items</div>
                        <div class="text-xs text-muted fw-semibold">
                            You have no {{ currentcat }}{{ currentcat !== 'pants' ? 's' : null }}.
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
    {{ userAvatar.color_head }}
    {{ userAvatar.color_torso }}
    {{ userAvatar.color_left_arm }}
    {{ userAvatar.color_right_arm }}
    {{ userAvatar.color_left_leg }}
    {{ userAvatar.color_right_leg }}

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
