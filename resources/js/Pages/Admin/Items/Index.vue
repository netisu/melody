<script setup lang="ts">
import { route } from 'ziggy-js';

import { usePage } from "@inertiajs/vue3";
import AdminNav from "@/Components/LayoutParts/Admin/AdminNav.vue";
import AppHead from "@/Components/AppHead.vue";
import Pagination from "@/Components/Pagination.vue";
import Navbar from "@/Components/LayoutParts/Navbar.vue";
import Footer from "@/Components/LayoutParts/Footer.vue";
import Sidebar from "@/Components/LayoutParts/Sidebar.vue";
</script>

<template>
    <AppHead pageTitle="Item Index" :description="usePage<any>().props.site.name + ' Administration'"
        :url="route('admin.items.page')" />
    <Navbar />
    <Sidebar>
        <div class="cell medium-3">
            <AdminNav />
        </div>
        <div class="cell medium-9">
            <div class="flex-container align-justify align-middle mb-1">
                <div class="text-xl fw-semibold">
                    Items
                </div>
                <div class="mt-2 align-middle flex-container align-center">
                    <Link as="button" :href="route(`admin.items.create.create-item`)" class="btn btn-info btn-sm">
                        New Item
                    </Link>
                </div>
            </div>
            <div class="card-body">
                <div class="grid-x grid-margin-x" v-if="usePage<any>().props.items?.['data']?.['length']">
                    <template>
                        <div v-for="item in usePage<any>().props.items.data" class="cell large-2 medium-3 small-6">
                            <div class="d-block" :href="route(`admin.items.manage-item`, { id: item.id })">
                                <Link class="p-2 mb-1 card card-inner position-relative">
                                    <img :src="item.thumbnail" />
                                </Link>
                                <p class="text-body text-md fw-semibold text-truncate">
                                    {{ item.name }}
                                </p>
                                <p class="text-md text-muted text-truncate">
                                    {{ item.description ? item.description : "This item does not have an description" }}
                                </p>
                                <p v-if="item.isOffsale" class="text-sm text-danger text-truncate">
                                    This item is offsale.
                                </p>
                            </div>
                        </div>
                    </template>
                </div>
                <Pagination v-if="usePage<any>().props.items?.['data']?.['length']"
                    v-bind:pagedata="usePage<any>().props.items">
                </Pagination>
                <div v-else>
                    <div class="pb-0 card-body">
                        <div class="gap-2 mb-2 text-center flex-container flex-dir-column">
                            <i class="text-5xl fas fa-person-fairy text-info"></i>
                            <div style="line-height: 16px">
                                <div class="text-xs fw-bold text-info">
                                    No items
                                </div>
                                <div class="text-info fw-semibold">
                                    <p class="text-xs">There are no items.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </Sidebar>
    <Footer />
</template>
