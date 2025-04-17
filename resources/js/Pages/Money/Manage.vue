<script setup lang="ts">
import Navbar from '@/Components/LayoutParts/Navbar.vue'
import Sidebar from '@/Components/LayoutParts/Sidebar.vue'
import { usePage, usePoll } from '@inertiajs/vue3';
import { route } from 'momentum-trail';
import AppHead from "@/Components/AppHead.vue";
import Footer from '@/Components/LayoutParts/Footer.vue';
import { ref } from 'vue';
import type { Ref } from 'vue';

const ActiveCategory: Ref<string> = ref("Information");

function setActiveCategory(category): void {
    ActiveCategory.value = category;
};
usePoll(10000, { only: ['purchases'] })
</script>

<template>
    <AppHead pageTitle="Money" :description="'Manage your finances on ' + usePage<any>().props.site.name + '.'"
        :url="route('my.money.page')" />
    <Navbar />
    <Sidebar>
        <div class="cell medium-3">
            <div class="mb-2 text-xl fw-semibold">Manage Your Finances</div>
            <ul class="tabs flex-dir-column">
                <li class="tab-item">
                    <a href="#" class="tab-link squish" @click="setActiveCategory('Information')"
                        :class="{ active: 'Information' === ActiveCategory }">
                        Information
                    </a>
                    <a href="#" class="tab-link squish" @click="setActiveCategory('Convert')"
                        :class="{ active: 'Convert' === ActiveCategory }">
                        Convert
                    </a>
                </li>
            </ul>
        </div>
        <div class="cell medium-8">
            <div v-if="ActiveCategory === 'Information'">
                <div class="grid-x grid-margin-x">
                    <div class="cell medium-6">
                        <div class="card card-body">
                            <div class="align-middle w-100 h-100 flex-container flex-dir-column align-center">
                                <div class="text-3xl text-warning">
                                    <i class="text-3xl fad fa-coins "></i>
                                    {{ usePage<any>().props.auth.user.coins }}

                                </div>
                                <div class="text-sm fw-semibold text-warning">
                                    Coins
                                </div>
                                <button class="text-info text-sm">
                                    {{ usePage<any>().props.auth.user.next_currency_payout }}
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="cell medium-6">

                        <div class="card card-body">
                            <div class="align-middle w-100 h-100 flex-container flex-dir-column align-center">
                                <div class="text-3xl text-success">
                                    <i class="text-3xl fad fa-money-bill-1-wave "></i>

                                    {{ usePage<any>().props.auth.user.bucks }}
                                </div>
                                <div class="text-sm fw-semibold text-success">
                                    Bucks
                                </div>
                                <button class="text-info text-sm">
                                    {{ usePage<any>().props.auth.user.next_currency_payout }}
                                </button>
                            </div>

                        </div>
                    </div>
                </div>
                <div class="mb-2 text-xl fw-semibold">Purchases</div>
                <div class="card">
                <div class="card-body" v-for="purchase in usePage<any>().props.purchases.data">
                    <div class="gap-2 align-middle flex-container">
                        <img :src="purchase.thumbnail" width="70" height="70" />
                        <div class="flex-wrap col-md-4 d-flex justify-content-center align-content-start"
                            style="flex-direction: column;">
                            <div class="text-start">
                                <Link class="text-md" :href="route(`store.item`, { id: purchase.id })">
                                {{ purchase.name }}
                                </Link>
                                <div></div>
                                <p class="text-sm fw-semibold" :class="{
                                    'text-warning': purchase.currency_used === 'coins',
                                    ' text-success': purchase.currency_used === 'bucks'
                                }">
                                    <i class="fad" :class="{
                                        ' fa-coins': purchase.currency_used === 'coins',
                                        '  fa-money-bill-1-wave': purchase.currency_used === 'bucks'
                                    }"></i>

                                    {{ purchase.price + " " + purchase.currency_used }}
                                </p>
                                <div class="text-xs text-muted fw-semibold text-body">
                                    {{ purchase.DateHum }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="divider mx-1 my-3"></div>
                </div>
            </div>
                <div :class="{ divider: usePage<any>().props.purchases.data.length > 0 }" class="mx-1 my-3"></div>
                <Pagination v-if="usePage<any>().props.purchases.data.length" class="mx-1 my-3"
                    :pagedata="usePage<any>().props.purchases"></Pagination>
            </div>
            <div v-if="ActiveCategory === 'Convert'">
                <div class="card card-body">
                    test2
                </div>
            </div>
        </div>
    </Sidebar>
    <Footer />
</template>
