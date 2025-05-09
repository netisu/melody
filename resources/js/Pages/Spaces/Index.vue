<script setup lang="ts">
import { ref } from "vue";
import { Link } from "@inertiajs/vue3";
import axios from "axios";
import { route } from 'ziggy-js';

import Navbar from "@/Components/LayoutParts/Navbar.vue";
import AppHead from "@/Components/AppHead.vue";
import Footer from "@/Components/LayoutParts/Footer.vue";
import Sidebar from "@/Components/LayoutParts/Sidebar.vue";
import VLazyImage from "v-lazy-image";

defineProps({
  spaces: { type: Object, required: true },
  search: { type: String, required: false },
});

// Define the SpaceSearchResult interface
interface SpaceSearchResult {
  url: string;
  thumbnail: string;
  name: string;
  // Add other properties if needed
}
const SpaceSearchLoading = ref(false);

const search = ref("");
// Update the type of results to be an array of SpaceSearchResult
const results = ref<SpaceSearchResult[]>([]);

const performSearch = async () => {
  if (search.value !== "") {
    SpaceSearchLoading.value = true;
    await axios
      .get(route("spaces.page", { search: search.value }))
      .then((response) => {
        // Assuming the response.data is an array of SpaceSearchResult
        results.value = response.data.results;
        SpaceSearchLoading.value = false;
      })
      .catch((err) => console.log(err));
  } else {
    SpaceSearchLoading.value = true;
    results.value = [];
  }
};
</script>
<template>
    <AppHead pageTitle="Spaces" description="Find a space to join or create one!" :url="route(`spaces.page`)" />

    <Navbar />
    <Sidebar>
        <div class="cell large-12">
            <div class="mb-2 align-middle grid-x">
                <div class="cell large-6">
                    <div class="mb-2 text-xl fw-semibold"><i class="text-xl fad fa-planet-ringed"></i>&nbsp;Spaces</div>
                </div>
                <div class="cell large-6">
                    <div class="gap-2 align-middle flex-container-lg">
                        <input type="text" class="mb-2 form form-xs form-has-section-color" placeholder="Search..."
                            name="search" v-model="search" @input="performSearch" />
                        <div class="mb-2 flex-container flex-child-grow">
                            <input type="submit" class="btn btn-xs btn-success w-100" value="Search" />
                        </div>
                        <div class="mb-2 flex-container flex-child-grow">
                            <Link :href="route(`spaces.create.page`)" class="text-center btn btn-info btn-xs flex-child-grow"><i
                                class="fad fa-planet-ringed"></i>&nbsp; Create Space</Link>
                        </div>
                    </div>
                </div>
            </div>
            <div v-if="spaces.data.length" class="grid-x grid-margin-x grid-padding-y">
                <div class="cell large-3 medium-6 small-3" v-for="(space, index) in spaces.data" :key="index">
                    <a href="#" class="d-block squish">
                        <v-lazy-image sizes="(max-width: 320px) 280px, 440px" :src="space.thumbnail"
                            class="mb-2 space-thumbnail" />
                        <div style="line-height: 18px">
                            <div class="d-block fw-semibold text-body">
                                <Link :href="route('spaces.view', { id: space.id, slug: space.slug })"
                                    style="color: inherit; font-weight: 600">{{ space.name }}</Link>
                            </div>
                            <div class="text-xs text-muted fw-semibold text-truncate">
                                {{ space.description ?? "This space does not have a description." }}
                            </div>
                            <div class="mt-1 text-xs fw-semibold text-muted">
                                {{ space.member_count }} Members <span class="mx-1">&bullet;</span>Created
                                {{ space.DateHum }}
                            </div>
                        </div>
                    </a>
                </div>
            </div>
            <div v-else class="">
                <div class="pb-0 card-body">
                    <div class="gap-3 mb-2 text-center flex-container flex-dir-column">
                        <i class="text-5xl fad fa-face-sad-cry text-muted"></i>
                        <div style="line-height: 16px">
                            <div class="text-xs fw-bold text-muted text-uppercase">
                                No spaces found.
                            </div>
                            <div class="text-muted fw-semibold">
                                <p class="text-xs">
                                    There are currently no spaces found, maybe you should create one!
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
