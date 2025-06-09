<script setup lang="ts">
import Navbar from '@/Components/LayoutParts/Navbar.vue'
import Sidebar from '@/Components/LayoutParts/Sidebar.vue'
import { usePage } from '@inertiajs/vue3';
import { route } from "ziggy-js";
import AppHead from "@/Components/AppHead.vue";
import Footer from '@/Components/LayoutParts/Footer.vue';
import { ref, onMounted, watch, computed } from 'vue'; // Import onMounted and watch
import type { Ref } from 'vue';
import axios from 'axios';
import ItemCardSkeleton from '@/Components/ItemCardSkeleton.vue';
import JsonPagination from '@/Components/JsonPagination.vue';

interface CategoryConfig {
    name: string;
    internal: string;
    icon: string;
}

interface CategoryGroup {
    icon: string;
    categories: CategoryConfig[];
}

interface InventoryItem {
    id: number;
    name: string;
    description: string;
    item_type: string;
    thumbnail: string;
    is_exclusive?: boolean;
    creator: {
        id: number | null;
        username: string;
        is_verified?: boolean;
    };
}

interface PaginatedResponse {
    data: InventoryItem[];
    current_page: number;
    last_page: number;
    total: number;
    from?: number;
    to?: number;
    per_page?: number;
    path?: string;
    links?: any[];
}

const pageProps = usePage<any>().props;
const inventoryCategories = pageProps.inventoryCategories as Record<string, CategoryGroup>;
const activeInternalCategory: Ref<string> = ref("hat"); // Default to 'hat'

const CategoryItems: Ref<PaginatedResponse | null> = ref(null);
const loading: Ref<boolean> = ref(true);

function setActiveCategory(categoryInternalKey: string): void {
    activeInternalCategory.value = categoryInternalKey;
    getItemList(categoryInternalKey, 1);
}

const getItemList = async (category: string, page: number = 1) => {
    try {
        loading.value = true;
        const response = await axios.get(
            route(`api.avatar.items`, { userId: pageProps.user.id, category: category, page: page })
        );
        const data = response.data;
        CategoryItems.value = data;
        loading.value = false;
    } catch (error) {
        loading.value = false;
        console.error("Error fetching items:", error);
    }
};

const handlePageClick = (page: number) => {
    getItemList(activeInternalCategory.value, page);
};

const hasNoItems = computed(() => !loading.value && (CategoryItems.value?.data?.length === 0 || !CategoryItems.value));
const skeletonCount = computed(() => loading.value ? 12 : 0);
// --- Lifecycle Hooks and Watchers ---
onMounted(() => {
    let defaultCategoryFound = false;
    for (const groupKey in inventoryCategories) {
        if (inventoryCategories[groupKey].categories && inventoryCategories[groupKey].categories.length > 0) {
            activeInternalCategory.value = inventoryCategories[groupKey].categories[0].internal;
            defaultCategoryFound = true;
            break;
        }
    }
    if (!defaultCategoryFound && Object.keys(inventoryCategories).length > 0) {
        activeInternalCategory.value = 'hat';
    }

    getItemList(activeInternalCategory.value, 1);
});

watch(activeInternalCategory, (newCategory) => {
    if (!CategoryItems.value || newCategory !== activeInternalCategory.value) {
        getItemList(newCategory, 1);
    }
});

</script>

<template>
    <AppHead :pageTitle="usePage<any>().props.user.username + '\'s Inventory'"
        :description="'View all items in  ' + usePage<any>().props.user.username + '\'s inventory.'"
        :url="route('user.inventory', { username: usePage<any>().props.user.username })" />
    <Navbar />
    <Sidebar>
        <div class="cell medium-12 mb-2">
            <Link :href="route('user.profile', { username: usePage<any>().props.user.username })"
                class="text-info text-lg"><i class="fad fa-arrow-left"></i>&nbsp;{{ 'Back to ' + usePage<any>
                    ().props.user.username }}</Link>
        </div>
        <div class="cell medium-3 mb-5">
            <ul class="tabs flex-dir-column">
                <template v-for="(group, groupName) in inventoryCategories" :key="groupName">
                    <li class="tab-group-title">
                        <span class="text-xs fw-semibold text-muted text-uppercase">
                            <i :class="group.icon" class="me-1"></i>{{ groupName }}
                        </span>
                    </li>

                    <li class="tab-item" v-for="(subCategory, key) in group" :key="key">
                        <template v-if="key !== 'icon'">
                            <a href="#" class="tab-link squish" @click="setActiveCategory(subCategory.internal)"
                                :class="{ 'active': subCategory.internal === activeInternalCategory }">
                                <span>{{ subCategory.name }}</span>
                                <span class="tab-icon">
                                    <i :class="subCategory.icon"></i>
                                </span>
                            </a>
                        </template>
                    </li>
                </template>
            </ul>
        </div>
        <div class="cell medium-9">
            <div class="grid-x grid-margin-x">
                <template v-if="loading">
                    <ItemCardSkeleton v-for="n in skeletonCount" :key="n" />
                </template>
                <div class="cell large-3 medium-3 small-6" v-for="item in CategoryItems?.data" :key="item.id">
                    <Link :href="route('store.item', { item: item.id })" class="d-block">
                    <div class="p-2 mb-1 card card-item position-relative">
                        <div style="
                                    position: absolute;
                                    bottom: 10px;
                                    left: 10px;
                                ">
                        </div>
                        <img src="item.thumbnail" id="item.thumbnail" />
                    </div>
                    <div class="text-body fw-semibold text-truncate">
                        Tophat
                    </div>
                    </Link>
                    <div class="text-xs fw-semibold text-truncate">
                        <span class="text-muted">By:</span>&nbsp;
                        <Link>
                        aeo<i class="fas fa-shield-check text-success ms-1"></i></Link>
                    </div>
                </div>
                <JsonPagination v-if="CategoryItems && CategoryItems.last_page > 1 && !loading"
                    @page-clicked="handlePageClick" :pagedata="CategoryItems" />
            </div>
            <div v-if="!loading || hasNoItems" class="card card-body">
                <div class="gap-3 mb-2 text-center flex-container flex-dir-column">
                    <i class="text-5xl fad text-info fa-person-fairy"></i>
                    <div style="line-height: 16px">
                        <div class="text-md fw-bold text-info">
                            No Items
                        </div>
                        <div class=" text-sm text-info">
                            {{ usePage<any>().props.user.username }} has no {{ activeInternalCategory.replace(/_/g, " ")
                            }}{{ activeInternalCategory !== "pants" ? "s" : null }}.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </Sidebar>
    <Footer />
</template>
