<script setup lang="ts">
import axios from "axios";
import { route } from "momentum-trail"
import AdminNav from "@/Components/LayoutParts/Admin/AdminNav.vue";
import Navbar from "@/Components/LayoutParts/Navbar.vue";

import { usePage } from "@inertiajs/vue3";
import Sidebar from "@/Components/LayoutParts/Sidebar.vue";
import Footer from "@/Components/LayoutParts/Footer.vue";

import AppHead from "@/Components/AppHead.vue";
import { ref, watch } from "vue";
import VLazyImage from "v-lazy-image";


const MaintenanceActive = ref(usePage<any>().props.siteSettings.inMaintenance);
const ForumActive = ref(usePage<any>().props.siteSettings.discussion_enabled);
const MarketActive = ref(usePage<any>().props.siteSettings.market_enabled);
const CustomizationActive = ref(usePage<any>().props.siteSettings.customization_enabled);
const SpacesActive = ref(usePage<any>().props.siteSettings.spaces_enabled);


const EnableMaintenance = async () => {
    try {
        await axios.post(route(`api.admin.enableMaintenance`));
        MaintenanceActive.value = 1;
    } catch (err) {
        console.error(err);
    }
};

const DisableMaintenance = async () => {
    try {
        await axios.post(route(`api.admin.disableMaintenance`));
        MaintenanceActive.value = 0;
    } catch (err) {
        console.error(err);
    }
};
watch(MaintenanceActive, (newValue, oldValue) => {
    console.log("Maintenance variable changed from", oldValue, "to", newValue);
});
</script>

<template>
    <AppHead pageTitle="Admin Dashboard" :description="usePage<any>().props.site.name + ' Administration'"
        :url="route('admin.page')" />
    <Navbar />
    <Sidebar>
        <div class="cell medium-3">
            <AdminNav />
        </div>
        <div class="cell medium-9">
            <div class="gap-3 align-middle flex-container">
                <Link :href="route(`user.profile`, { username: usePage<any>().props.auth.user.username })">
                <v-lazy-image :src="usePage<any>().props.auth.user.headshot" width="100"
                    class="border headshot flex-child-shrink img-fluid rounded-circle border-secondary bg-dark v-lazy-image v-lazy-image-loaded flex-wrap col-md-4 d-flex justify-content-center align-content-start"
                    style="flex-direction: column;" alt="Avatar" src-placeholder="/assets/img/dummy_headshot.png" />
                </Link>
                <div class="flex-wrap col-md-4 d-flex justify-content-center align-content-start"
                    style="flex-direction: column;">
                    <div class="text-start">
                        <span class="text-base text-2xl fw-semibold">
                            Welcome Back
                        </span>
                        <div></div>
                        <Link class="text-base text-2xl fw-semibold"
                            :href="route(`user.profile`, { username: usePage<any>().props.auth.user.username })">
                        {{ "@" + usePage<any>().props.auth.user.username }}
                            </Link>
                        <div></div>
                        <span class="badge badge-danger w-100">{{ usePage<any>().props.auth?.user?.position }}</span>
                    </div>
                </div>
            </div>
            <div class="section no-divider">
                <div class="mb-1 text-xl fw-semibold">
                    Quick Actions
                </div>
                    <div class="gap-3 text-start">
                        <div style="line-height: 16px">
                            <span class="text-lg text-base fw-semibold">
                                Maintenance is currently {{ MaintenanceActive ? "ON" : "OFF" }}!
                            </span>
                        </div>
                        <button class="btn btn-info" v-if="usePage<any>().props.stats.canControlMaintenance"
                                @click="!MaintenanceActive ? EnableMaintenance() : DisableMaintenance()">
                                Turn
                                Maintenance {{
                                    !MaintenanceActive ? "ON" : "OFF" }}?
                            </button>
                    </div>
                    <div class="divider mx-1 my-2" />
                <div class="flex-wrap gap-1 flex-container align-start">
                        <button v-if="usePage<any>().props.stats.canControlMarketPurchases"
                            :class="{ 'is-active': MarketActive ? true : false }" class="btn btn-info">{{ MarketActive ?
                                'Enable' : 'Disable' }}
                            Market</button>
                        <button v-if="usePage<any>().props.stats.canControlDiscussion"
                            :class="{ 'is-active': ForumActive ? true : false }" class="btn btn-info">{{ ForumActive ?
                                'Enable' : 'Disable' }}
                            Discussion</button>
                        <button v-if="usePage<any>().props.stats.canControlCustomization"
                            :class="{ 'is-active': CustomizationActive ? true : false }" class="btn btn-info">{{
                                CustomizationActive ?
                                    'Enable' : 'Disable' }} Customization</button>
                        <button v-if="usePage<any>().props.stats.canControlSpaces"
                            :class="{ 'is-active': SpacesActive ? true : false }" class="btn btn-info">{{ SpacesActive ?
                                'Enable' : 'Disable' }}
                            Spaces</button>
                    </div>
                </div>
            </div>
    </Sidebar>
    <Footer />
</template>
