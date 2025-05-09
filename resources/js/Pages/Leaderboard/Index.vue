<script setup lang="ts">
import Navbar from "@/Components/LayoutParts/Navbar.vue";
import Sidebar from "@/Components/LayoutParts/Sidebar.vue";
import Footer from "@/Components/LayoutParts/Footer.vue";
import AppHead from "@/Components/AppHead.vue";
import { route } from 'ziggy-js';
// If you're using the 'route' function from 'momentum-trail'
import { usePage } from "@inertiajs/vue3";

defineProps({
    XP: { type: Array },
    Bucks: { type: Array },
});

const TopList = (indexKey: Number) => {
    if (indexKey === 1) {
        return "text-warning";
    } else if (indexKey === 2) {
        return "text-info";
    } else if (indexKey === 3) {
        return "text-danger";
    } else {
        return "text-body";
    }
};
</script>

<template>
    <AppHead pageTitle="Leaderboard" :description="'Top users on ' + usePage<any>().props.site.name + '.'"
        :url="route('leaderboard.page')" />
    <Navbar />
    <Sidebar>
        <div class="cell large-6">
            <div class="mb-2 text-xl fw-semibold">Top Level Leaders</div>
            <div class="card">
                <div class="pb-0 card-body">
                    <div class="gap-3 text-center mb-3 flex-container flex-dir-column" v-if="!XP?.['length']">
                        <i class="text-5xl fad fa-arrow-up text-muted"></i>
                        <div style="line-height: 16px">
                            <div class="text-xs fw-bold text-muted text-uppercase">
                                No Leaders
                            </div>
                            <div class="text-muted fw-semibold">
                                <p class="text-xs">
                                    There are currently no leaders.
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3 row thread" :key="index" v-for="(top, index) in XP">
                        <div class="gap-3 align-middle flex-container">
                            <div class="text-2xl fw-semibold" :class="TopList(Number(index + 1))">
                                {{ "#" + Number(index + 1) }}
                            </div>
                            <div class="flex-wrap col-md-4 d-flex justify-content-center align-content-start"
                                style="flex-direction: column">
                                <div class="text-start">
                                    <Link :href="route('user.profile', {
                                        username: top.username,
                                    })
                                        " class="text-md">
                                    {{ top.username }}
                                    </Link>
                                    <div></div>
                                    <div class="text-sm fw-semibold text-muted">
                                        {{
                                        "Rank Lvl. " +
                                        top.experience.level_id
                                        }}<span class="mx-1">&bullet;</span>{{
                                        top.experience.experience_points +
                                        "XP"
                                        }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mx-1 my-3 divider"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="cell large-6">
            <div class="mb-2 text-xl fw-semibold">Top Cash Earners</div>
            <div class="card">
                <div class="pb-0 card-body">
                    <div class="gap-3 text-center mb-3 flex-container flex-dir-column" v-if="!Bucks?.['length']">
                        <i class="text-5xl fad fa-coins text-muted"></i>
                        <div style="line-height: 16px">
                            <div class="text-xs fw-bold text-muted text-uppercase">
                                No Leaders
                            </div>
                            <div class="text-muted fw-semibold">
                                <p class="text-xs">
                                    There are currently no leaders.
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3 row thread" :key="index" v-for="(topBucks, index) in Bucks">
                        <div class="gap-3 align-middle flex-container">
                            <div class="text-2xl fw-semibold" :class="TopList(Number(index + 1))">
                                {{ "#" + Number(index + 1) }}
                            </div>
                            <div class="flex-wrap col-md-4 d-flex justify-content-center align-content-start"
                                style="flex-direction: column">
                                <div class="text-start">
                                    <Link :href="route('user.profile', {
                                        username: topBucks.username,
                                    })
                                        " class="text-md">
                                    {{ topBucks.username }}
                                    </Link>
                                    <div></div>
                                    <div class="text-sm fw-semibold text-success">
                                        <i class="fas fa-money-bill-1-wave">&nbsp;</i>{{ topBucks.bucks }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mx-1 my-3 divider"></div>
                    </div>
                </div>
            </div>
        </div>
    </Sidebar>
    <Footer />
</template>
