<script setup lang="ts">
import Navbar from '@/Components/LayoutParts/Navbar.vue';
import Sidebar from '@/Components/LayoutParts/Sidebar.vue';
import Footer from '@/Components/LayoutParts/Footer.vue';
import { route } from "ziggy-js";

import { usePage } from '@inertiajs/vue3';

interface BanObject {
  type: string;
  note: string;
  cleared: boolean;
  DateHum: string;
  exactTime: string;
}

defineProps<{
  ban: BanObject;
}>();
</script>

<template>
    <Navbar>
        <Sidebar>
            <div class="cell medium-7">
            <div class="mb-3 card card-body">
                <div class="mb-3">
                    <div class="text-xl" style="line-height: 16px">
                        <div class="mb-1 fw-semibold">
                            <div class="text-xl">{{ ban.type }}</div>
                        </div>
                        <div class="text-sm text-muted fw-semibold mb-3">
                            We noticed some activity on your account that are violate the Terms of Service.
                            To keep using {{ usePage<any>().props.site.name }}, make sure you're following the community guidelines.
                            Treat others with respect, avoid sharing inappropriate content, and play fair.
                            A quick refresh on the  <a class="link text-primary">rules</a> can help you stay in the game!
                            <div class="p-2 mt-3 mb-1 card card-body card-item position-relative">
                                {{ ban.note }}
                            </div>
                        </div>
                        <div class="divider-m0"></div>
                        <div class="mt-2 mb-3 text-center text-md text-info fw-semibold">
                            You can reinstate your account on {{ ban.DateHum }} at {{ ban.exactTime }}.
                        </div>
                        <div class="min-w-0 gap-2 align-center flex-container">
                            <Link
            :href="route('auth.logout')"
            method="post"
            class="min-w-0 btn btn-danger btn-xs text-truncate"
            as="button"
          >
            <i class="fad fa-right-from-bracket"></i>&nbsp;Logout
          </Link>
                        <button v-if="ban.cleared" class="btn btn-xs btn-info">
                            Reinstate your account
                        </button>
                        </div>
                    </div>
                </div>
            </div>
            </div>
        </Sidebar>
    </Navbar>
    <Footer />
</template>
