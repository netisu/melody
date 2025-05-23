<script setup lang="ts">
import { usePage } from "@inertiajs/vue3";

const props = usePage<any>().props;

defineProps({
    isLanding: { type: Boolean, default: false },
});
</script>

<template>
    <!-- ======= Footer ======= -->
    <footer class="footer">
        <main
        class="container py-3">
            <div class="align-middle grid-x grid-margin-x">
                <div class="cell large-6">
                    <div class="mb-1 text-lg fw-semibold mb-lg-0">
                        Copyright &copy; {{ new Date().getFullYear() }}
                        <img :src="props.site.icon" width="24" style="margin-top: -2px"  />
                        {{ props.site.name }}. All rights reserved.
                    </div>
                    <div class="mb-2 flex-container-lg mb-lg-0">
                        <a href="#" class="text-sm footer-link fw-semibold"><i class="fad fa-scroll"></i> TERMS OF SERVICE</a>
                        <a href="#" class="text-sm footer-link fw-semibold"><i class="fad fa-lock"></i> PRIVACY POLICY</a>
                        <a href="#" class="text-sm footer-link fw-semibold"><i class="fad fa-hard-hat"></i> JOBS</a>
                        <a href="#" class="text-sm footer-link fw-semibold"><i class="fad fa-envelope"></i> CONTACT</a>
                    </div>
                    <div class="mb-2 flex-container-lg mb-lg-0">
                        <div class="text-xs text-muted fw-semibold">{{ usePage<any>().props.site.version }}</div>
                    </div>
                </div>
                <div class="cell large-6">
                    <div class="gap-3 flex-container align-right align-center-sm">
                        <a v-show="props.site.socials.discord" :href="props.site.socials.discord" class="text-2xl footer-media text-muted"
                            content="Join us on Discord" v-tippy>
                            <i class="fab fa-discord"></i>
                        </a>
                        <a v-show="props.site.socials.twitter" :href="props.site.socials.twitter" class="text-2xl footer-media text-muted"
                            content="Follow us on Twitter" v-tippy>
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a v-show="props.site.socials.twitch" :href="props.site.socials.twitch" class="text-2xl footer-media text-muted"
                            content="Follow us on Twitch" v-tippy>
                            <i class="fab fa-twitch"></i>
                        </a>
                        <a v-show="props.site.socials.tiktok" :href="props.site.socials.tiktok" class="text-2xl footer-media text-muted"
                            content="Follow us on TikTok" v-tippy>
                            <i class="fab fa-tiktok"></i>
                        </a>
                        <a v-show="props.site.socials.facebook" :href="props.site.socials.facebook" class="text-2xl footer-media text-muted"
                            content="Follow us on Facebook" v-tippy>
                            <i class="fab fa-facebook"></i>
                        </a>
                    </div>
                </div>
            </div>
        </main>
    </footer><!-- End  Footer -->
</template>

<script lang="ts">
import { directive } from "vue-tippy";

export default {
    directives: {
        tippy: directive,
    },
    methods: {
        showModal(modalId: string): void {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.toggle("active");
            }
        },
        addActiveClass(elementId: string): void {
            const element = document.getElementById(elementId);
            if (element) {
                element.classList.toggle('active');
            }
        },
        sidebarOpen(): void {
            const sidebar = document.getElementById('sidebar');
            if (sidebar) {
                sidebar.classList.toggle('show-for-large');
            }
        },
        addActiveClassSSInput(elementId: string): void {
            const element = document.getElementById(elementId) as HTMLInputElement;
            const isEmpty = (str: string): boolean => !str.trim().length;

            if (element) {
                element.addEventListener('input', function () {
                    if (isEmpty(this.value)) {
                        return;
                    } else {
                        element.classList.toggle('hide');
                    }
                });
            }
        }
    }
};
</script>
