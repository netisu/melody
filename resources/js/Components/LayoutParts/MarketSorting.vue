<script setup lang="ts">
import { ref, watch, inject } from "vue";
import axios from "axios";
import { route } from "ziggy-js";

defineProps<{
    selectedSubCategory: string;
    selectedCategory: string;
}>();

const items = inject<any>("items");
const ItemLoading = inject<any>("ItemLoading");
const searchQuery = ref("");
const selectedSort = ref("default");

const sortOptions = [
    { text: "Advanced Sorting...", value: "default", disabled: true },
    { text: "Price: Low to High", value: "price_asc" },
    { text: "Price: High to Low", value: "price_desc" },
    { text: "Newest", value: "newest" },
    { text: "Oldest", value: "oldest" },
    // Add more sorting options as needed
];
async function fetchFilteredItems() {
    try {
        if (ItemLoading) ItemLoading.value = true;

        const params: Record<string, any> = {};
        const parentSelectedInternal = inject<string>(
            "selectedInternalCategory"
        );
        if (parentSelectedInternal) {
            params.category = parentSelectedInternal;
        } else {
            params.category =
                props.selectedSubCategory || props.selectedCategory;
        }

        if (searchQuery.value) {
            params.search = searchQuery.value;
        }
        if (selectedSort.value !== "default") {
            params.sort = selectedSort.value;
        }
        emit("filterChanged", params);
    } catch (error) {
        console.error("Error fetching filtered items:", error);
    } finally {
        if (ItemLoading) ItemLoading.value = false;
    }
}

watch([searchQuery, selectedSort], () => {
    fetchFilteredItems();
});

// Emit event for parent to listen to
const emit = defineEmits(["filterChanged"]);
</script>

<template>
    <div class="mb-2 align-middle grid-x">
        <div class="cell large-3">
            <div v-if="selectedCategory" class="text-xl fw-semibold">
                {{ selectedCategory }}
            </div>
            <div
                :class="[
                    !selectedCategory
                        ? 'text-xl fw-semibold'
                        : 'mb-2 text-xs text-muted fw-semibold',
                ]"
            >
                {{ selectedSubCategory }}
            </div>
        </div>
        <div class="cell large-9">
            <div class="gap-2 align-middle flex-container-lg">
                <input
                    type="text"
                    class="mb-2 form form-xs form-has-section-color"
                    placeholder="Search..."
                    v-model.lazy="searchQuery"
                    @keyup.enter="fetchFilteredItems"
                />
                <select
                    class="mb-2 form form-xs form-select form-has-section-color"
                    v-model="selectedSort"
                    @change="fetchFilteredItems"
                >
                    <option
                        v-for="option in sortOptions"
                        :key="option.value"
                        :value="option.value"
                        :disabled="option.disabled"
                    >
                        {{ option.text }}
                    </option>
                </select>
                <div class="mb-2 flex-container flex-child-grow">
                    <input
                        type="submit"
                        class="btn btn-xs btn-success w-100"
                        value="Search"
                    />
                </div>
                <div class="mb-2 flex-container flex-child-grow">
                    <a
                        href="#"
                        class="text-center btn btn-info btn-xs flex-child-grow"
                        >Buy Currency</a
                    >
                </div>
            </div>
        </div>
    </div>
</template>
