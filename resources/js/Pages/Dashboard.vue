<script setup lang="ts">
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { route } from "ziggy-js";
import { useForm, usePage } from '@inertiajs/vue3';
import Navbar from '@/Components/LayoutParts/Navbar.vue';
import Sidebar from '@/Components/LayoutParts/Sidebar.vue';
import AppHead from '@/Components/AppHead.vue';
import Footer from '@/Components/LayoutParts/Footer.vue';
import NewsCard from '@/Components/NewsCard.vue';
import StatusCard from '@/Components/StatusCard.vue';
import NewsCardSkeleton from '@/Components/NewsCardSkeleton.vue';
import VLazyImage from "v-lazy-image";
import throttle from "lodash/throttle";
import DummyAvatar from "@/Images/dummy.png";

const form = useForm({
    message: '',
});

defineProps<{
    recommendations: Array<any>,
}>();

const statuses = ref([]);

const getStatuses = async () => {
    try {
        const response = await axios.get(route(`api.dashboard.statuses`));
        statuses.value = response.data;
    } catch (error) {
        console.error('Error fetching statuses:', error);
    }
};

const initialData = statuses;
function createStatusUpdate(status: string) {
    return {
        name: usePage<any>().props.auth.user.username,
        dname: usePage<any>().props.auth.user.display_name,
        DateHum: "Just Now",
        message: status,
        thumbnail: usePage<any>().props.auth.user.headshot,
    };
}

const JSONALERT = ref<{ message: string; type: string } | null>(null);
const addStatus = throttle((status: string): void => {
    const newStatus = createStatusUpdate(status);
    initialData.value.unshift(newStatus); // Push the single StatusObject
    axios.post(route(`my.dashboard.validate`), {
        message: status,
    }).then((response) => {
        const jsonData = response.data;

        JSONALERT.value = jsonData;
    }).catch((error) => {
        console.error("Error:", error);
    });
}, 15000);


const getXPProgressWidth = (currentxp, nextxp) => {
    const progress = (currentxp / nextxp) * 100;
    return `${progress}%`;
};

onMounted(() => {
    getStatuses();
});
</script>
<template>
    <AppHead pageTitle="Dashboard"
        :description="'Welcome back to ' + usePage<any>().props.site.name + ',' + usePage<any>().props.site.name + '.'"
        :url="route('my.dashboard.page')" />
    <Navbar />
    <Sidebar :JSONALERT="JSONALERT">
        <div class="cell medium-12 pb-2">
            <div class="pb-2">
                <h3 class="text-lg font-medium">
                    <div class=" text-xl fw-semibold">Dashboard</div>
                </h3>
                <p class="text-sm text-muted">
                    {{ "Welcome Back, " + usePage<any>().props.auth.user.username + '.' }}
                </p>
            </div>
            <div class="divider mx-1 my-1 mb-2" />
        </div>
        <div class="cell medium-3">
            <div class="gap-2 mb-3 align-middle card card-body flex-container flex-dir-column">
                <v-lazy-image :src="usePage<any>().props.auth.user.thumbnail" width="200" alt="Avatar"
                    :src-placeholder="DummyAvatar" />
                <div class="text-center fw-semibold text-md" style="line-height: 16px">
                    <div :class="usePage<any>().props.auth.user.staff ? 'text-danger' : 'text-success'">
                        {{ usePage<any>().props.auth.user.staff ? usePage<any>().props.auth.user.position : 'Netizen' }}
                    </div>
                </div>
                <div class="divider w-100"></div>
                <div class="w-100">
                    <div class="gap-3 align-middle flex-container">
                        <i class="text-3xl fas fa-medal text-info" style="width: 40px"></i>
                        <div class="w-100">
                            <div class="mb-1 flex-container align-justify">
                                <div class="text-xs fw-bold text-muted text-uppercase">
                                    Rank {{ 'Lvl. ' + usePage<any>().props.auth.user.level }}
                                </div>
                                <div class="text-xs fw-bold text-muted text-uppercase">
                                    {{ usePage<any>().props.auth.user.xp + '/' + usePage<any>
                                        ().props.nextlevelxp }} EXP
                                </div>
                            </div>
                            <div class="progress-bar">
                                <span class="progress"
                                    :style="{ width: getXPProgressWidth(usePage<any>().props.auth.user.xp, usePage<any>().props.nextlevelxp) }"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="text-xl fw-semibold mb-1">
                The {{ usePage<any>().props.site.name }} Times
            </div>
            <div class="card card-body">
                <template v-if="NewsLoading">
                    <NewsCardSkeleton />
                    <NewsCardSkeleton />
                    <NewsCardSkeleton />
                </template>
                <div class="gap-3 text-center flex-container flex-dir-column" v-else-if="!Feed">
                    <i class="text-5xl fas fa-face-sleeping text-muted"></i>
                    <div style="line-height: 18px">
                        <div class="text-xs fw-bold text-muted text-uppercase">
                            No Blog Posts
                        </div>
                        <div class="text-xs text-muted fw-semibold">
                            When we publish a post it will appear here.
                        </div>
                    </div>
                </div>
                <div class="text-sm text-danger fw-semibold" v-else-if="Feed.error">
                    {{ Feed.error }}
                </div>
                <NewsCard v-else v-for="Bpost in Feed" :key="Bpost.link" :link="Bpost.link" :creator="Bpost.creator"
                    :image="Bpost.image" :title="Bpost.title" :desc="Bpost.desc" :date="Bpost.date" />
            </div>
        </div>
        <div class="cell medium-9">
            <div class="section no-divider">
                <div class="mb-1 text-xl fw-semibold show-for-small-only">
                    Feed
                </div>
                <div class="mb-3">
                    <div class="position-relative">
                        <textarea class="mb-2 form form-has-button form-has-section-color pe-5" rows="5"
                            v-model="form.message"
                            :placeholder="'What\'s good, ' + usePage<any>().props.auth.user.username + '?'"></textarea>
                        <input type="submit" @click="addStatus(form.message)" class="btn btn-success btn-sm" style="
                            position: absolute;
                            bottom: 10px;
                            right: 10px;
                        " />
                    </div>
                </div>
                <div class="mb-1 text-xl fw-semibold">Posts</div>
                <div class="card card-body mb-3">
                    <div class="gap-3 text-center flex-container flex-dir-column" v-if="!statuses.length">
                        <i class="text-5xl fas fa-face-sleeping text-muted"></i>
                        <div style="line-height: 16px">
                            <div class="text-xs fw-bold text-muted text-uppercase">
                                No Posts
                            </div>
                            <div class="text-xs text-muted fw-semibold">
                                Start following players and their posts will appear here.
                            </div>
                        </div>
                    </div>
                    <StatusCard v-else v-for="(status, index) in initialData" :key="index" :DisplayName="status.dname"
                        :Thumbnail="status.thumbnail" :name="status.name" :message="status.message"
                        :date="status.DateHum" />
                </div>
                <div class="mt-2 mb-1 text-xl fw-semibold">
                    Today's Recommendations
                </div>
                <div class="divider mx-1 my-2" />
                <div class="text-sm text-muted mt-2">
                    <div class="mb-2 text-sm text-body fw-semibold">
                        <div v-if="recommendations.length" class="grid-x grid-margin-x">
                            <div class="cell large-2 medium-3 small-6" v-for="suggestion in recommendations">
                                <Link :href="route('store.item', { id: suggestion.id })" class="d-block">
                                <div class="p-2 mb-1 card card-inner position-relative">
                                    <img :src="suggestion.thumbnail">
                                </div>
                                <div class="text-body text-md fw-semibold text-truncate">
                                    {{ suggestion.name }}
                                </div>
                                </Link>
                            </div>
                        </div>
                        <div v-else class="text-sm fw-bold text-muted">
                            There is no Recommendations.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </Sidebar>
    <Footer />
</template>
<script lang="ts">
export default {
    data() {
        return {
            Feed: <any>[],
            NewsLoading: false,
            StatusLoading: false,
        }
    },
    mounted() {
        this.getFeed()
    },

    methods: {
        async getFeed() {
            try {
                this.NewsLoading = true;
                const response = await axios.get(route('api.rss')); // Assuming 'api.rss' is the correct API route
                this.Feed = response.data; // Use response.data directly to get the response content
                this.NewsLoading = false;
            } catch (error) {
                this.NewsLoading = true;
                console.error(error);
            }
        },
    }
}
</script>
