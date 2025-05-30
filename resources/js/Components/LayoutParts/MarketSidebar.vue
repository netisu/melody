<script lang="ts" setup>
import { ref, defineProps, defineEmits, onMounted } from "vue";
import type { Ref } from "vue";

interface Category {
    name: string;
    internal: string; // Add the 'internal' property
    icon: string;
    // Add other subcategory properties as needed
}
interface SubCategory {
    name: string;
    internal: string; 
    icon: string;
}

interface Props {
    categories: Record<string, Category>;
    selectCategory: string;
    selectSubCategory: string;
}

const emit = defineEmits<{
    categorySelected: [category: Category];
    subCategorySelected: [category: SubCategory];
}>();

const props = defineProps<Props>();
const selectedCategory: Ref<Category | string> = ref(null);
const openCategories = ref([]);

function handleCategorySelect(category: string) {
    selectedCategory.value = category;
    emit("categorySelected", category);
}

function toggleCategory(categoryName: string) {
    if (openCategories.value.includes(categoryName)) {
        // Remove if open
        openCategories.value = openCategories.value.filter(
            (name) => name !== categoryName
        );
    } else {
        // Add if closed
        openCategories.value.push(categoryName);
    }
}

onMounted(() => {
  // Select the first subcategory of the first category on mount, if categories exist
  const firstCategoryName = Object.keys(props.categories)[0];
  if (
    firstCategoryName &&
    props.categories[firstCategoryName] &&
    props.categories[firstCategoryName][0] // Access the first subcategory
  ) {
    handleCategorySelect(props.categories[firstCategoryName][0])
  }
});
</script>
<template>
    <div
        class="collapsible"
        v-for="(category, categoryName) in categories"
        :class="{ active: openCategories.includes(categoryName) }"
        :key="categoryName"
    >
        <button
            @click="toggleCategory(categoryName)"
            class="d-block w-100 market-section-item"
        >
            <div class="align-middle flex-container align-justify">
                <div class="text-sm">
                    <i
                        :class="category.icon"
                        class="text-md icon-fixed-width"
                    ></i>
                    {{ categoryName }}
                </div>
                <i class="text-xs fas fa-chevron-down text-muted"></i>
            </div>
        </button>
        <div class="mt-1 mb-1 collapsible-menu">
            <button
                v-for="(subCategory, index) in category"
                @click="handleCategorySelect(subCategory)"
                :class="{ active: selectedCategory === subCategory }"
                :key="index"
                class="text-xs market-section-item"
            >
                {{ subCategory.name }}
            </button>
        </div>
    </div>
</template>
