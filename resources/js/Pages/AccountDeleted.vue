<script setup lang="ts">
import Navbar from '@/Components/LayoutParts/Navbar.vue';
import Sidebar from '@/Components/LayoutParts/Sidebar.vue';
import Footer from '@/Components/LayoutParts/Footer.vue';
import { route } from "ziggy-js";

import { usePage } from '@inertiajs/vue3';

interface BanObject {
    id: Number;
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
            <div class="cell medium-10">
                <div class="flex-container align-justify align-middle mb-1">
                    <div class="mb-1 mt-2">
                        <div class="text-sm text-muted fw-semibold mb-1">Case {{ '#' + ban.id }}</div>
                        <div class="text-xl fw-semibold mb-1">
                            <i class="fas fa-exclamation-circle me-1"></i>
                            {{ ban.type }}
                        </div>
                    </div>
                    <button v-if="ban.cleared" class="btn btn-info btn-xs">Reactivate Account</button>
                </div>
                <div class="card card-body">
                    We have noticed that you recently violated some of {{ usePage<any>().props.site.name }}'s <a>Community rules</a>.

                    <p> These rules are put in place to ensure {{ usePage<any>().props.site.name }} remains a safe place for everybody. Please
                        make
                        sure your
                        behavior on {{ usePage<any>().props.site.name }} is appropriate and frendly for users of all ages to prevent further
                        action.
                    </p>
                    <div class="mx-1 my-3 divider"></div>
                    As a result of these infractions we have taken the following action against your account:
                    <br>
                    <div class="text-danger mt-2 mb-2">
                        <i class="fas fa-gavel"></i>&nbsp;{{ ban.type }}
                    </div>
                    <div class="mb-2">
                        <i class="fas fa-message-lines"></i>&nbsp;Additional comments provided by Moderator:
                        <div class="card mt-2 card-item position-relative card-body">
                            {{ ban.note }}
                        </div>
                    </div>
                    <div class="mx-1 my-3 divider"></div>
                    <div class="mt-2 mb-3 text-center text-md text-info fw-semibold">
                        You can reinstate your account on {{ ban.DateHum }} at {{ ban.exactTime }}.
                    </div>
                    <div class="flex-container-lg gap-2">

                        <button class="btn btn-info btn-xs btn-block mb-2 mb-lg-0">
                            <i class="fas fa-scroll me-1"></i>
                            Community rules
                        </button>
                        <button class="btn btn-danger btn-xs btn-block">
                            <i class="fas fa-gavel me-1"></i>
                            Appeal Action
                        </button>
                    </div>
                </div>
            </div>
        </Sidebar>
    </Navbar>
    <Footer />
</template>
