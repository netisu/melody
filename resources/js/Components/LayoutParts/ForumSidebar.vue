<script setup lang="ts">
import { route, current } from 'momentum-trail';

const { section_one, section_two, section_three } = defineProps([
    'section_one',
    'section_two',
    'section_three',
]);
function setActiveTopCat(categoryName): void {
    // Select the element with the matching ID
    const element = document.getElementById(categoryName);
    if (element) {
        // Add or remove the desired class
        element.classList.add('active'); // Replace 'active' with your class name
    } else {
        console.warn(`Element with ID '${categoryName}' not found.`);
    }
}
</script>

<template>
    <div class="gap-2 mb-2 align-middle flex-container align-justify">
        <div class="text-2xl fw-semibold"><i class="text-xl fad fa-message-code"></i>&nbsp;Forum</div>
    </div>
    <div class="collapsible" @click="setActiveTopCat('Official')" id="Official">
        <button class="d-block w-100 market-section-item">
            <div class="align-middle flex-container align-justify">
                <div class="text-sm text-danger"> <i class="fa-solid fa-megaphone fa-xs"></i>&nbsp;Official</div>
                <i class="text-xs fas fa-chevron-down text-muted"></i>
            </div>
        </button>
        <div class="mb-2 collapsible-menu">
            <Link as="button" v-for="Official in section_one" :key="Official.id"
                :href="route('forum.page', { id: Official.id })" class="text-xs market-section-item"
                :class="[current('forum.page', Official.id) ? 'active' : '']">
            <i v-if="Official.role_required_to_post != null" class="fas fa-lock"></i>&nbsp;{{ Official.name }}
            </Link>
        </div>
    </div>
    <div class="collapsible" @click="setActiveTopCat('Community')" id="Community">
        <button class="d-block w-100 market-section-item">
            <div class="align-middle flex-container align-justify">
                <div class="text-sm text-info"> <i class="fa-solid fa-party-horn fa-xs"></i>&nbsp;Community</div>
                <i class="text-xs fas fa-chevron-down text-muted"></i>
            </div>
        </button>
        <div class="mb-2 collapsible-menu">
            <Link as="button" v-for="Community in section_two" :key="Community.id"
                :href="route('forum.page', { id: Community.id })" class="text-xs market-section-item"
                :class="[current('forum.page', Community.id) ? 'active' : '']">
            <i v-if="Community.role_required_to_post != null" class="fas fa-lock"></i>&nbsp;{{ Community.name }}
            </Link>
        </div>
    </div>
    <div class="collapsible" @click="setActiveTopCat('Serious')" id="Serious">
        <button class="d-block w-100 market-section-item">
            <div class="align-middle flex-container align-justify">
                <div class="text-sm text-warning"> <i class="fa-solid fa-triangle-exclamation fa-xs"></i>&nbsp;Serious</div>
                <i class="text-xs fas fa-chevron-down text-muted"></i>
            </div>
        </button>
        <div class="mb-2 collapsible-menu">
            <Link as="button"  v-for="Serious in section_three" :key="Serious.id"
                :href="route('forum.page', { id: Serious.id })" class="text-xs market-section-item"
                :class="[current('forum.page', Serious.id) ? 'active' : '']">
            <i v-if="Serious.role_required_to_post != null" class="fas fa-lock"></i>&nbsp;{{ Serious.name }}
            </Link>
        </div>
    </div>
</template>
