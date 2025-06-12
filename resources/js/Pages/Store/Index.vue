<script setup lang="ts">
import { ref, computed, provide } from "vue";
import axios from "axios";
import { usePage } from "@inertiajs/vue3";
import MarketSidebar from "@/Components/LayoutParts/MarketSidebar.vue";
import MarketSorting from "@/Components/LayoutParts/MarketSorting.vue";
import AppHead from "@/Components/AppHead.vue";
import Navbar from "@/Components/LayoutParts/Navbar.vue";
import Sidebar from "@/Components/LayoutParts/Sidebar.vue";
import Footer from "@/Components/LayoutParts/Footer.vue";
import { route } from "ziggy-js";
import SuperBanner from "@/Components/LayoutParts/SuperBanner.vue";
import marketLogo from "@/Images/site-banners/market-title.png";
import ItemCardSkeleton from "@/Components/ItemCardSkeleton.vue";
import VLazyImage from "v-lazy-image";

const props = defineProps({
    categories: Object,
});

const items = ref<any>([]);
const categories = computed<any>(() => props.categories);

const ItemLoading = ref(false);

const selectedCategory = ref(""); // Ref to store the currently selected category
const selectedSubCategory = ref(""); // Ref to store the currently selected category
const currentSelectedInternal = ref(""); // To store the 'internal' ID of the selected category/subcategory

// Fetch items for the selected category
async function fetchItems(selectedcat: any, filters?: { search?: string; sort?: string }) {
        try {

            let mainCategoryName = "";
            if (props.categories) {
                for (const mainCategoryKey of Object.keys(props.categories)) {
                    const mainCategory = props.categories[mainCategoryKey];
                    const foundSubcategory = Object.values(mainCategory).find(
                        (sub: any) => sub.internal === selectedcat.internal
                    );

                    if (foundSubcategory) {
                        mainCategoryName = mainCategoryKey; // Found the parent category name
                        break; // Exit loop once found
                    }
                }
            }

            selectedCategory.value = mainCategoryName;
            selectedSubCategory.value = selectedcat.name;
            currentSelectedInternal.value = selectedcat.internal; // Store the internal ID


            ItemLoading.value = true;

            const apiParams: Record<string, any> = { category: selectedcat.internal };
            if (filters?.search) {
                apiParams.search = filters.search;
            }
            if (filters?.sort) {
                apiParams.sort = filters.sort;
            }

            const response = await axios.get(
                route(`api.store.items`, apiParams) // Pass all collected parameters
            );

            items.value = response.data;
            ItemLoading.value = false;
        } catch (error) {
            console.error(error);
        }
}

// Function to handle category selection
function selectCategory(category: string) {
    selectedCategory.value = category;
}

// Function to handle subcategory selection
function selectSubCategory(SubCategory: string) {
    selectedSubCategory.value = SubCategory;
    ItemLoading.value = true;
}

function onImgErrorSmall(id) {
    let source = document.getElementById(id) as HTMLImageElement;
    source.src = "/assets/img/dummy-error.png";
    source.onerror = null;

    return true;
}
// Computed properties
const itemRoute = (itemId) => route(`store.item`, { id: itemId });
const creatorRoute = (username) =>
    route(`user.profile`, { username: username });

provide("selectedCategory", selectedCategory);
provide("selectedInternalCategory", currentSelectedInternal);
provide("selectedSubCategory", selectedSubCategory);
</script>

<template>
    <AppHead
        pageTitle="Market"
        description="Level up your style."
        :url="route(`store.page`)"
    />
    <Navbar :landing="false" />
    <Sidebar
        image="null"
        :superBanActive="true"
        :OfficialImageBackground="true"
    >
        <template #SuperBanner>
            <SuperBanner>
                <template #bannerAsset>
                    <i class="text-5xl fad fa-bag-shopping text-info"></i>
                </template>

                <template #bannerName>
                    <div class="text-xl" style="line-height: 16px">
                        <v-lazy-image
                            :src="marketLogo"
                            alt="Market"
                            style="width: auto; height: 50px"
                        />
                    </div>
                </template>
                <template #bannerDescription>
                    <span class="text-muted text-wrap fw-semibold"
                        >Craving something new? Create it or find it here.</span
                    >
                </template>
                <template #bannerButtons>
                    <Link
                        v-if="usePage<any>().props.auth.user"
                        as="button"
                        class="text-xl text-success squish"
                        :href="route(`store.create.page`)"
                        content="Create Item"
                        v-tippy="{ placement: 'bottom' }"
                        style="outline: none"
                    >
                        <i class="fad fa-square-pen"></i>
                    </Link>
                </template>
            </SuperBanner>
        </template>

        <div class="cell large-2">
            <MarketSidebar
                :categories="categories"
                :selectCategory="selectCategory"
                :selectSubCategory="selectSubCategory"
                @categorySelected="fetchItems"
            />
        </div>
        <div class="cell large-10">
            <MarketSorting
                :selectedCategory="selectedCategory"
                :selectedSubCategory="selectedSubCategory"
            />
            <div
                v-if="ItemLoading || (items && items.length > 0)"
                class="grid-x grid-margin-x grid-padding-y"
            >
                <template v-if="ItemLoading">
                    <ItemCardSkeleton v-for="n in 12" :key="n" />
                </template>
                <div
                    v-else
                    class="cell large-2 medium-3 small-6"
                    v-for="(item, index) in items"
                    :key="index"
                >
                    <Link :href="itemRoute(item.id)" class="d-block">
                        <div class="p-2 mb-1 card card-item position-relative">
                            <div
                                style="
                                    position: absolute;
                                    bottom: 10px;
                                    left: 10px;
                                "
                            >
                                <div
                                    v-if="item.in_event"
                                    class="mb-1 badge badge-warning fw-semibold"
                                >
                                    <i class="fad fa-calendar-star"></i>
                                </div>
                                <div
                                    v-else-if="item.rare"
                                    class="mb-1 badge badge-info fw-semibold"
                                >
                                    <i class="fad fa-stars"></i>
                                </div>
                                <div
                                    v-else-if="item.time_off_sale != null"
                                    class="mb-1 badge badge-warning fw-semibold"
                                >
                                    <i class="fad fa-clock"></i>
                                </div>
                                <div
                                    v-else-if="!item.onsale"
                                    class="mb-1 badge badge-secondary fw-semibold"
                                >
                                    <i class="fad fa-wheat-slash"></i>
                                </div>
                            </div>
                            <img
                                :src="item.thumbnail"
                                :id="item.thumbnail"
                                @error="onImgErrorSmall(item.thumbnail)"
                            />
                        </div>
                        <div class="text-body fw-semibold text-truncate">
                            {{ item.name }}
                        </div>
                    </Link>
                    <div class="text-xs fw-semibold text-truncate">
                        <span class="text-muted">By:</span>&nbsp;
                        <Link
                            :class="{ 'text-danger': item.creator.staff }"
                            :href="creatorRoute(item.creator.username)"
                        >
                            {{ "@" + item.creator.username
                            }}<i
                                v-if="item.creator.verified"
                                class="fas fa-shield-check text-success ms-1"
                            ></i
                        ></Link>
                    </div>
                </div>
            </div>
            <div v-else-if="items.message" class="">
                <div class="pb-0 card-body">
                    <div
                        class="gap-3 mb-2 text-center flex-container flex-dir-column"
                    >
                        <i class="text-5xl fas fa-store-slash text-muted"></i>
                        <div style="line-height: 16px">
                            <div class="text-xs fw-bold text-muted">
                                Looks like a gremlin got into the code again!
                                We're sending our best programmers with bug
                                spray to fix the issue
                            </div>
                            <div class="text-muted fw-semibold">
                                <p class="text-xs">{{ items.message }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div v-else class="">
                <div class="pb-0 card-body">
                    <div
                        class="gap-3 mb-2 text-center flex-container flex-dir-column"
                    >
                        <i class="text-5xl fad fa-person-fairy text-info"></i>
                        <div style="line-height: 16px">
                            <div
                                class="text-xs fw-bold text-info text-uppercase"
                            >
                                No {{ selectedSubCategory }}
                            </div>
                            <div class="text-info fw-semibold">
                                <p class="text-xs">
                                    There are currently no
                                    {{ selectedSubCategory }}.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </Sidebar>
    <Footer />
</template>
