<script setup lang="ts">
import AppHead from "@/Components/AppHead.vue";
import Navbar from "@/Components/LayoutParts/Navbar.vue";
import Sidebar from "@/Components/LayoutParts/Sidebar.vue";
import VLazyImage from "v-lazy-image";
//import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <AppHead title="Aeo's Game" />
    <Navbar />
    <Sidebar image="/assets/img/site-banners/landing-melody.png" :alertsEnabled="false" :superBanActive="true"
        :OfficialImageBackground="false">
        <template>
            <portal to="bannerAsset">
                <v-lazy-image src="/assets/img/dummy-error.png" width="100" class="space-image" alt="Game Thumbnail"
                    src-placeholder="/assets/img/dummy-error.png" />
            </portal>
            <portal to="bannerButtons">
                <Link class="btn btn-info btn-xs text-white squish" as="button" style="outline: none">
                <i class="fad fa-gamepad-modern"></i> Play Game
                </Link>
            </portal>
            <portal to="bannerName">
                <div class="text-xl" style="line-height: 16px">Aeo's Game</div>
            </portal>
            <portal to="bannerDescription">
                Created by: <a href="#" class="text-link text-truncate">aeo</a>
            </portal>
        </template>
        <div class="cell medium-12">
            <div class="mb-1 align-middle navbar-grid">
                <div class="cell large-12">
                    <div class="grid-x grid-margin-x grid-padding-y">
                        <div class="mb-1 cell large-3 medium-6 small-6">
                            <div class="text-center">
                                <div class="card-body">
                                    <h3 class="text-muted"><i class="fad fa-swords"></i> Combat</h3>
                                    <p>Genere</p>
                                </div>
                            </div>
                        </div>
                        <div class="mb-1 cell large-3 medium-6 small-6">
                            <div class="text-center">
                                <div class="card-body">
                                    <h3 class="text-muted"><i class="fad fa-user"></i> 5</h3>
                                    <p>Active</p>
                                </div>
                            </div>
                        </div>
                        <div class="mb-1 cell large-3 medium-6 small-6">
                            <div class="text-center">
                                <div class="card-body">
                                    <h3 class="text-muted"><i class="fad fa-heart"></i> 1</h3>
                                    <p>Favorite</p>
                                </div>
                            </div>
                        </div>
                        <div class="mb-1 cell large-3 medium-6 small-6">
                            <div class="text-center">
                                <div class="card-body">
                                    <h3>21</h3>
                                    <p>Communities</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mb-2 text-xl fw-semibold">Description</div>
            <div class="card card-body">
                <span href="#" class="text-md">Thumbnail </span>
            </div>
        </div>
        <div class="cell medium-4">
            <div class="mb-2 text-xl fw-semibold">Active Servers</div>
            <div class="card card-body">
                <div class="section-borderless">
                    <div class="section">
                        <a class="gap-2 mt-2 mb-2 align-middle flex-container align-justify squish">
                            <div class="headshot-group">
                                <div class="headshot">
                                    <img src="/assets/img/dummy_headshot.png" width="50" />
                                </div>
                                <div class="headshot">
                                    <img src="/assets/img/dummy_headshot.png" width="50" />
                                </div>
                                <div class="headshot">
                                    <span
                                        class="text-lg text-muted flex-container text-center align-center flex-child-grow">
                                        50+
                                    </span>
                                </div>
                            </div>

                            <a class="min-w-0 btn btn-info btn-sm text-truncate hide-for-medium-only">
                                <i class="fad fa-user"></i> Join
                            </a>
                        </a>

                        <!-- Add divider after each server except the last one -->
                        <div class="divider w-100" />
                    </div>
                </div>
            </div>
        </div>
        <div class="cell medium-8">
            <div class="mb-2 text-xl fw-semibold">Comments</div>
            <div class="card card-body">
                <div class="gap-3 section flex-container flex-dir-column-sm">
                    <div class="mx-auto flex-child-grow" style="width: 100px">
                        <Link href="#" class="text-sm text-center d-block squish">
                        <v-lazy-image src="/assets/img/dummy_headshot.png" width="60" class="headshot" alt="Avatar"
                            src-placeholder="/assets/img/dummy_headshot.png" />
                        <div style="line-height: 16px">
                            <div class="text-truncate">aeno</div>
                            <div class="text-xs text-muted text-truncate">
                                @aeo
                            </div>
                        </div>
                        </Link>
                    </div>
                    <div class="card card-body card-inner w-100">
                        <div class="align-middle flex-container align-justify">
                            <div class="w-100">
                                <div class="text-xs text-muted">
                                    <i class="far fa-clock me-1"
                                        style="vertical-align: middle;margin-top: -2.5px;font-size: 10px;"></i>
                                    Posted 2m ago
                                </div>
                                <div>
                                    placeholder comment
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </Sidebar>
</template>
