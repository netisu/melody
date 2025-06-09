<script setup lang="ts">
import Navbar from "@/Components/LayoutParts/Navbar.vue";
import { usePage } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import Sidebar from "@/Components/LayoutParts/Sidebar.vue";
import Footer from "@/Components/LayoutParts/Footer.vue";
</script>

<template>
    <AppHead pageTitle="Provider Menu"
        :description="'Use a Provider to Login/Register on ' + usePage<any>().props.site.name + ''"
        :url="route('auth.login.page')" />
    <Navbar />
    <Sidebar>
        <div class="cell medium-12 pb-2">
            <div class="pb-2">
                <h3 class="text-lg font-medium">
                    <div class="text-xl fw-semibold">Account Providers</div>
                </h3>
                <p class="text-sm text-muted">
                   Login using your preferred providers here.
                </p>
            </div>
            <div class="divider mx-1 my-1 mb-2" />
        </div>

        <div class="cell medium-6 mb-2">
            <div class="card card-body">
                <div class="gap-2 cell small-7 medium-9 flex-container">
                    <Link as="button" :href="route(`auth.login.google.auth`)" class="flex items-center w-full">
                    <i class="fab fa-google text-2xl text-info"></i>
                    <div class="flex-wrap col-md-4 d-flex justify-content-center align-content-start"
                        style="flex-direction: column">
                        <div class="text-start">
                            <div class="text-md text-info">
                                Google
                            </div>
                            <div></div>
                            <div class="text-sm fw-semibold text-muted">
                                Login using your google account here.
                            </div>
                            <div></div>
                        </div>
                    </div>
                    </Link>
                </div>
            </div>
        </div>
        <div class="cell medium-6 mb-2">
            <div class="card card-body card-status">
                <div class="gap-2 cell small-7 medium-9 flex-container">
                    <Link  as="button" :href="route(`auth.login.discord.auth`)" class="flex items-center w-full">
                    <i class="fab fa-discord text-2xl  text-info"></i>
                    <div class="flex-wrap col-md-4 d-flex justify-content-center align-content-start"
                        style="flex-direction: column">
                        <div class="text-start">
                            <div class="text-md  text-info">
                                Discord
                            </div>
                            <div></div>
                            <div class="text-sm fw-semibold text-muted">
                                Login using your discord account here.
                            </div>
                            <div></div>
                        </div>
                    </div>
                    </Link>
                </div>
            </div>
        </div>
    </Sidebar>
    <Footer />
</template>
