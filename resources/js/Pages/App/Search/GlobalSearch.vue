<script setup lang="ts">
import AppHead from "@/Components/AppHead.vue";
import Navbar from "@/Components/LayoutParts/Navbar.vue";
import Sidebar from "@/Components/LayoutParts/Sidebar.vue";
import Footer from "@/Components/LayoutParts/Footer.vue";
import { ref, watch, onMounted, computed } from "vue";
import { usePage } from "@inertiajs/vue3";
import axios from "axios";
import { debounce } from "lodash";
import VLazyImage from "v-lazy-image";

const props = defineProps({
    initialSearchQuery: String,
    initialResults: Array,
});

interface SearchResult {
    id: number;
    url: string;
    icon: string;
    image: string;
    name: string;
    description: string;
    type: string;
}

const searchQuery = ref(props.initialSearchQuery || "");
const results = ref<SearchResult[]>([]);

const isLoading = ref(false);

const SearchDescription = computed(() => {
    const siteName = usePage<any>().props.site.name;
    return searchQuery.value
        ? `Search Results for '${searchQuery.value}'`
        : `Start searching across all of ${siteName}!`;
});

const performSearch = debounce(async () => {
    if (searchQuery.value.length < 2 && searchQuery.value.length !== 0) {
        results.value = [];
        return;
    }

    isLoading.value = true;
    try {
        const response = await axios.get(route("api.search"), {
            params: { search: searchQuery.value },
        });
        results.value = response.data;
    } catch (error) {
        console.error("Error fetching search results:", error);
        results.value = [];
    } finally {
        isLoading.value = false;
        const url = new URL(window.location.href);
        url.searchParams.set("search", searchQuery.value);
        window.history.pushState({}, "", url);
    }
}, 300);

watch(searchQuery, performSearch);

onMounted(() => {
    if (searchQuery.value) {
        performSearch();
    }
});
</script>

<template>
    <AppHead pageTitle="Search" :description="SearchDescription" :url="route(`search.page`)" />

    <Navbar />
    <Sidebar>
        <div class="cell medium-12 pb-2">
            <div class="pb-2">
                <h3 class="text-lg font-medium">
                    <div class="text-xl fw-semibold">Search</div>
                </h3>
                <p class="text-sm text-muted">
                    {{
                        searchQuery
                            ? ` Displaying results for: "${searchQuery}"`
                            : `Start searching across all of ${usePage<any>().props.site.name}!`
                    }}
                </p>
            </div>
            <div class="divider mx-1 my-1 mb-2" />
        </div>

        <div class="cell large-12">

            <form @submit.prevent="performSearch">
                <div class="gap-2 align-middle flex-container-lg">
                    <input type="text" v-model="searchQuery" placeholder="Search all results..."
                        class="mb-2 form form-has-section-color" />
                    <div class="mb-2 flex-container flex-child-grow">

                        <button type="submit" class="btn btn-info w-100" :disabled="isLoading">
                            {{ isLoading ? `Searching...` : `Search` }}
                        </button>
                    </div>
                </div>

            </form>

            <div v-if="isLoading" class="text-center text-gray-500">
                <p>Loading results...</p>
                <i class="fad fa-spinner fa-spin text-4xl mt-4"></i>
            </div>
            <div v-else-if="results.length > 0">
                <div class="grid-x grid-margin-x">
                <div v-for="result in results" :key="result.url + result.type + result.id" class="cell medium-6 mb-2">
                    <div class="card card-body card-status">

<div class="gap-2 cell small-7 medium-9 flex-container">
                                <Link :href="result.url" class="flex items-center w-full">
                                <v-lazy-image v-if="result.image" :src="result.image" width="45px"
                                    class="border img-fluid headshot rounded-circle border-secondary bg-dark"
                                    :alt="result.name" />
                                <i v-else-if="result.icon" :class="result.icon" class="text-2xl text-gray-600"></i>
                                <span v-else class="text-lg text-gray-500"><i
                                        class="fa-duotone fa-solid fa-seal-question"></i></span>
                                <div class="flex-wrap col-md-4 d-flex justify-content-center align-content-start"
                                    style="flex-direction: column">
                                    <div class="text-start">
                                        <div class="text-md">
                                            {{ result.name }}
                                        </div>
                                        <div></div>
                                        <div class="text-sm fw-semibold text-body">
                                            {{ result.description }}

                                        </div>
                                        <div></div>
                                    </div>
                                </div>
                                <div class="cell show-for-large small-5 medium-3 flex-container align-right gap-2">
                                    <div class="badge badge-info" style="height:25px;"> {{ result.type.replace("_", " ")
                                        }}</div>
                                </div>
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div v-else>
                <p class="text-gray-600">
                    No results found for "{{ searchQuery }}".
                </p>
            </div>
        </div>
    </Sidebar>
    <Footer />
</template>
