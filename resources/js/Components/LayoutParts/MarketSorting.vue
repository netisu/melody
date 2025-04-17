<script setup lang="ts">

defineProps<{
    selectedSubCategory: string,
    selectedCategory: string,
}>();


</script>

<template>
    <div class="mb-2 align-middle grid-x">
        <div class="cell large-3">
            <div v-if="selectedCategory" class="text-xl fw-semibold">
                {{ selectedCategory }}
            </div>
            <div :class="[!selectedCategory ? 'text-xl fw-semibold' : 'mb-2 text-xs text-muted fw-semibold']">
                {{ selectedSubCategory }}
            </div>
        </div>
        <div class="cell large-9">
            <div class="gap-2 align-middle flex-container-lg">
                <input type="text" class="mb-2 form form-xs form-has-section-color" placeholder="Search..." />
                <select class="mb-2 form form-xs form-select form-has-section-color">
                    <option value="1" selected disabled>
                        Advanced Sorting...
                    </option>
                </select>
                <div class="mb-2 flex-container flex-child-grow">
                    <input type="submit" class="btn btn-xs btn-success w-100" value="Search" />
                </div>
                <div class="mb-2 flex-container flex-child-grow">
                    <a href="#" class="text-center btn btn-info btn-xs flex-child-grow">Buy Currency</a>
                </div>
            </div>
        </div>
    </div>
</template>
