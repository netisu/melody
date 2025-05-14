<script setup lang="ts">
import { ref, onMounted } from "vue";
import type { Ref } from "vue";

interface Category {
    name: string;
    icon: string;
}

interface Props {
    categories: Record<string, any>; // Adjusted type to handle mixed structure
    selectCategory: Function; // or specify the expected type if it's not a function
}

const isOpen = ref<boolean>(false);
const { categories } = defineProps<Props>();
const selectedCategory = ref<Category | null>(null);
const selectedSubCategory: Ref<string> = ref("Accessories");
const emit = defineEmits(["categorySelected"]); // Define the emit function to emit custom events

// Function to handle category selection
function selectCategory(category: Category) {
    selectedCategory.value = category;
    // Emit the 'category-selected' event with the selected category
    emit("categorySelected", category);
}

function openCategory(categoryName: string): void {
    selectedSubCategory.value = categoryName;
    console.log(categoryName);
    // Select the element with the matching ID
    const element = document.getElementById(categoryName);
    console.log(element);
    isOpen.value == !isOpen.value;
    if (element) {
        if (isOpen) {
            // Add the active class only when `isOpen1 is false
            element.classList.add("active");
        } else if (!isOpen) {
            // Remove when `isOpen` is true
            element.classList.remove("active");
        }
    } else {
        console.warn(`Element with ID '${categoryName}' not found.`);
    }
}
onMounted(() => {
    if (categories?.["length"] > 0) {
        selectCategory(categories[1].name);
    }
});
</script>
<template>
    <div
        class="collapsible"
        v-for="(category, categoryName) in categories"
        @click="openCategory(categoryName)"
        :id="categoryName"
    >
        <button class="d-block w-100 market-section-item">
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
        <div
            v-if="selectedSubCategory === categoryName"
            :id="categoryName + '_List'"
            class="mt-1 mb-1 collapsible-menu"
        >
            <button
                v-for="(item, index) in category"
                @click="() => selectCategory(item)"
                :class="{ active: selectedCategory === item }"
                :key="index"
                class="text-xs market-section-item"
            >
                {{ item.name }}
            </button>
        </div>
    </div>
</template>
<script lang="ts">
import { directive } from "vue-tippy";

export default {
    directives: {
        tippy: directive,
    },
};
</script>
