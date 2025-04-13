<script setup lang="ts">
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { route } from 'momentum-trail';
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
import type { Ref } from 'vue';

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

const ActiveCategory: Ref<string> = ref("Feed");

function setActiveCategory(category): void {
    ActiveCategory.value = category;
};

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
    <AppHead pageTitle="Dashboard" :description="'Login to' + usePage<any>().props.site.name + '.'"
        :url="route('auth.login.page')" />
    <Navbar />
    <Sidebar :JSONALERT="JSONALERT">
        <div class="mb-4 cell medium-11">

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
                    </div>
                </div>
            </div>
        </div>
        <div class="cell medium-3">
            <div class="gap-2 mb-3 align-middle  card-body flex-container flex-dir-column">
                <v-lazy-image :src="usePage<any>().props.auth.user.thumbnail" width="200" alt="Avatar"
                    src-placeholder="/assets/img/dummy.png" />
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
        </div>
        <div class="cell medium-9">
            <div class="section">
                <ul class="tabs">
                    <li class="tab-item">
                        <a class="tab-link squish" :class="{ active: 'Feed' === ActiveCategory }" @click="
                            setActiveCategory('Feed')">Feed</a>
                    </li>
                    <li class="tab-item">
                        <a class="tab-link squish" :class="{ active: 'Stats' === ActiveCategory }" @click="
                            setActiveCategory('Stats')">Statistics</a>
                    </li>
                </ul>
            </div>
            <div class="section no-divider" v-if="ActiveCategory === 'Feed'">
                <div class="mb-1 text-xl fw-semibold">
                    The {{ usePage<any>().props.site.name }} Times
                </div>
                <div class="gap-2 cell medium-4 mb-1 align-justify  flex-container" v-if="NewsLoading">
                    <NewsCardSkeleton />
                    <NewsCardSkeleton />
                    <NewsCardSkeleton />

                </div>
                <div class="cell large-4 medium-4 small-1" v-else>

                    <div class="gap-3 text-center flex-container flex-dir-column" v-if="!Feed">
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
                    <div v-else class="grid-x grid-margin-x grid-padding-y">
                        <NewsCard class="cell large-4 medium-6 small-12" v-for="Bpost in Feed" :key="Bpost.link"
                            :link="Bpost.link" :creator="Bpost.creator" :image="Bpost.image" :title="Bpost.title"
                            :desc="Bpost.desc" :date="Bpost.date" />
                    </div>
                </div>
                <div class="mb-1 text-xl fw-semibold show-for-small-only">
                    Feed
                </div>
                <div class="mb-3">
                    <div class="position-relative">
                        <textarea class="mb-2 form form-has-button form-has-section-color pe-5" rows="5"
                            v-model="form.message" placeholder="How are you doing?"></textarea>
                        <input type="submit" @click="addStatus(form.message)" class="btn btn-success btn-sm has-ripple"
                            value="Post" style="
                            position: absolute;
                            bottom: 10px;
                            right: 10px;
                        " />
                    </div>
                </div>
                <div class="mb-1 text-xl fw-semibold">Posts</div>
                <div class="card card-body">

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
            </div>
            <div class="section no-divider" v-else-if="ActiveCategory === 'Stats'">
                <div class="grid-x grid-margin-x grid-padding-y mb-2">

                    <div class="card card-body cell large-4 medium-6 small-12">
                        <div class="flex-container">
                            <div class="text-start">

                                <div class="text-xs fw-semibold text-warning text-uppercase"><i
                                        class="fad fa-crown"></i> Total Sales</div>
                                <div class="mb-1 text-xl text-uppercase">{{ usePage<any>().props.totalSales
                                        }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="card card-body cell large-4 medium-6 small-12">
                        <div class="flex-container">
                            <div class="text-start">

                                <div class="text-xs fw-semibold text-success text-uppercase"><i class="fad fa-user"></i>
                                    Total Followers</div>
                                <div class="mb-1 text-xl text-uppercase">{{ usePage<any>().props.totalFollowers
                                        }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="card card-body cell large-4 medium-6 small-12">
                        <div class="flex-container">
                            <div class="text-start">

                                <div class="text-xs fw-semibold text-info text-uppercase"><i class="fad fa-message"></i>
                                    Total Posts</div>
                                <div class="mb-1 text-xl text-uppercase">{{ usePage<any>().props.totalPosts
                                        }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="card card-body cell large-4 medium-6 small-12"
                        v-if="usePage<any>().props.auth.user.staff">
                        <div class="flex-container">
                            <div class="text-start">
                                <div class="text-xs fw-semibold text-danger text-uppercase"><i class="fad fa-gavel"></i>
                                    Pending Assets</div>
                                <div class="mb-1 text-xl text-uppercase">{{ usePage<any>().props.auth.user.pendingAssets
                                        }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                    <div class="mb-1 text-xl fw-semibold">
                        Today's Recomendations
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
                                There is no recomendations.
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
