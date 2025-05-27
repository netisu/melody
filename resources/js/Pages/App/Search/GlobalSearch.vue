<script setup lang="ts">
import AppHead from '@/Components/AppHead.vue';
import Navbar from '@/Components/LayoutParts/Navbar.vue';
import Sidebar from '@/Components/LayoutParts/Sidebar.vue';
import Footer from '@/Components/LayoutParts/Footer.vue';
import { ref, watch, onMounted, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { debounce } from 'lodash';

const props = defineProps({
    initialSearchQuery: String,
    initialResults: Array,
});

const searchQuery = ref(props.initialSearchQuery || '');
const results = ref(props.initialResults || []);
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
        const response = await axios.get(route('api.search'), {
            params: { search: searchQuery.value }
        });
        results.value = response.data;
    } catch (error) {
        console.error('Error fetching search results:', error);
        results.value = [];
    } finally {
        isLoading.value = false;
        const url = new URL(window.location.href);
        url.searchParams.set('q', searchQuery.value);
        window.history.pushState({}, '', url);
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
     <AppHead pageTitle="Search"
        :description="SearchDescription"
        :url="route(`search.page`)" />

  <Navbar />
  <Sidebar>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Search Results
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">

                    <div class="mb-6">
                        <form @submit.prevent="performSearch" class="flex">
                            <input
                                type="text"
                                v-model="searchQuery"
                                placeholder="Search all results..."
                                class="flex-grow rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                            />
                            <button
                                type="submit"
                                class="ml-3 inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150"
                                :disabled="isLoading"
                            >
                                <span v-if="isLoading">Searching...</span>
                                <span v-else>Search</span>
                            </button>
                        </form>
                    </div>

                    <h3 v-if="searchQuery" class="text-lg font-medium text-gray-900 mb-4">
                        Displaying results for: "<span class="font-bold">{{ searchQuery }}</span>"
                    </h3>
                    <h3 v-else class="text-lg font-medium text-gray-900 mb-4">
                        All Searchable Items
                    </h3>

                    <div v-if="isLoading" class="text-center text-gray-500">
                        <p>Loading results...</p>
                        <i class="fas fa-spinner fa-spin text-4xl mt-4"></i>
                    </div>
                    <div v-else-if="results.length > 0">
                        <div v-for="result in results" :key="result.url + result.type + result.id" class="mb-4 p-3 border rounded-lg flex items-center shadow-sm">
                            <Link :href="result.url" class="flex items-center w-full">
                                <div class="mr-4 flex-shrink-0" :class="{'w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center': !result.image}">
                                    <img v-if="result.image" :src="result.image" :alt="result.name" class="w-10 h-10 rounded-full object-cover" />
                                    <i v-else-if="result.icon" :class="result.icon" class="text-2xl text-gray-600"></i>
                                    <span v-else class="text-lg text-gray-500">?</span>
                                </div>

                                <div class="flex-grow">
                                    <div class="font-semibold text-lg text-gray-800">{{ result.name }}</div>
                                    <div v-if="result.description" class="text-sm text-gray-600">{{ result.description }}</div>
                                </div>

                                <div class="flex-shrink-0 text-right">
                                    <div class="text-xs text-gray-500 capitalize">{{ result.type.replace('_', ' ') }}</div>
                                    <i class="fas fa-chevron-right text-gray-400 ml-2"></i>
                                </div>
                            </Link>
                        </div>
                    </div>
                    <div v-else>
                        <p class="text-gray-600">No results found for "{{ searchQuery }}".</p>
                    </div>
                </div>
            </div>
        </div>
        </Sidebar>
        <Footer />
</template>
