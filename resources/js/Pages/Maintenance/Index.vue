<script setup lang="ts">
import axios from "axios"; // Import Axios
import { route } from 'ziggy-js';

import AppHead from "@/Components/AppHead.vue";
import { router, usePage } from '@inertiajs/vue3';

const { props } = usePage<any>();
</script>
<style>
body {
  background-image: url('/assets/img/site-banners/netisuxTyphoon.png') !important;
  background-repeat: no-repeat;
}
.fa-gradient.text-success {
    background: -webkit-gradient(linear, left top, left bottom, from(var(--success-500)), to(#fff));
    -webkit-background-clip: text;
    background-clip: text;
    -webkit-text-fill-color: transparent;
}
.fa-gradient.text-danger {
    background: -webkit-gradient(linear, left top, left bottom, from(var(--danger-600)), to(#fff));
    -webkit-background-clip: text;
    background-clip: text;
    -webkit-text-fill-color: transparent;
}
.fa-gradient.text-warning {
    background: -webkit-gradient(linear, left top, left bottom, from(var(--warning-600)), to(#fff));
    -webkit-background-clip: text;
    background-clip: text;
    -webkit-text-fill-color: transparent;
}
</style>
<template>
    <AppHead pageTitle="Maintenence"
        :description="'We are working on ' + usePage<any>().props.site.name + ', We\'ll be back soon.'"
        :url="route(`maintenance.page`)" />
    <div class="modal" id="DeveloperCode">
        <form @submit.prevent="submitPassword">
            <div class="modal-card modal-card-body modal-card-sm">
                <div class="section-borderless">
                    <div class="gap-2 align-middle flex-container align-justify">
                        <div class="text-lg fw-semibold">Developer Access</div>
                        <button @click="showModal('DeveloperCode')" class="btn-circle"
                            data-toggle-modal="#DeveloperCode" style="margin-right: -10px">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                <div class="section-borderless">
                    <div class="mb-2">
                        <div class="text-xs fw-bold text-muted text-uppercase">
                            Enter Your Developer Code
                        </div>
                        <input class="form" type="password" placeholder="Developer Code" v-model="password" />
                    </div>
                    <div v-if="showError" class="text-xs text-danger fw-semibold">
                        {{ errorMsg }}
                    </div>
                    <div class="text-xs text-muted fw-semibold">
                        {{ usePage<any>().props.disclaimer }}
                    </div>
                </div>
                <div class="flex-wrap gap-2 flex-container justify-content-end section-borderless">
                    <button class="btn btn-secondary" @click="showModal('DeveloperCode')">
                        Cancel
                    </button>
                    <button class="btn btn-info" type="submit">Enter</button>
                </div>
            </div>
        </form>
    </div>
    <main class="container px-4 text-center flex-dir-column loading-page">
        <i class="fas fa-screwdriver-wrench fa-gradient text-success text-7xl " focusable="false" id="icon"
            @click="countClicks"></i>
        <h1 class="mt-3 mb-0">We'll be back soon!</h1>
        <h3 class="mt-0 text-muted">We're performing some maintenance at the moment.</h3>

        <div class="flex-wrap gap-2 flex-container justify-content-center align-center section-borderless">
            <a v-if="props.site.socials.discord" :href="props.site.socials.discord"
                class="text-2xl footer-media text-muted" content="Join us on Discord" v-tippy>
                <i class="fab fa-discord"></i>
            </a>
            <a v-if="props.site.socials.twitter" :href="props.site.socials.twitter"
                class="text-2xl footer-media text-muted" content="Follow us on Twitter" v-tippy>
                <i class="fab fa-twitter"></i>
            </a>
            <a v-if="props.site.socials.twitch" :href="props.site.socials.twitch"
                class="text-2xl footer-media text-muted" content="Follow us on Twitch" v-tippy>
                <i class="fab fa-twitch"></i>
            </a>
            <a v-if="props.site.socials.tiktok" :href="props.site.socials.tiktok"
                class="text-2xl footer-media text-muted" content="Follow us on TikTok" v-tippy>
                <i class="fab fa-tiktok"></i>
            </a>
            <a v-if="props.site.socials.facebook" :href="props.site.socials.facebook"
                class="text-2xl footer-media text-muted" content="Follow us on Facebook" v-tippy>
                <i class="fab fa-facebook"></i>
            </a>
        </div>
    </main>
</template>

<script lang="ts">
export default {
  data() {
    return {
      password: "",
      showError: false,
      errorMsg: "",
      activation: 0,
      classOptions: [
        "text-muted",
        "text-upgrade",
        "text-danger",
        "text-success",
        "text-warning",
      ],
    };
  },
  methods: {
    countClicks() {
      this.activation++;
      this.changeClassByNumber("icon", this.activation, this.classOptions);
      if (this.activation >= 5) {
        this.showModal("DeveloperCode");
      }
    },
    showModal(modalId: string): void {
      const modal = document.getElementById(modalId);
      if (modal) {
        modal.classList.toggle("active");
      }
    },
    changeClassByNumber(element, val, classes) {
      for (const className of classes) {
        const iconwheel = document.getElementById(element);
        if (!iconwheel) {
          return; // Exit the loop if iconwheel is not found
        }
        if (classes.length) {
          iconwheel.classList.add(classes[val]); // Add 'text-muted' class for val = 1
          return; // Exit the loop after adding the desired class
        } else if (val !== 1) {
          iconwheel.classList.remove(className); // Remove other classes if val != 1
        }
      }
    },

    submitPassword() {
      this.showError = false; // Reset error msg
      this.errorMsg = "";

      if (!this.password) {
        this.errorMsg = "Please provide a password.";
        this.showError = true;
        return;
      }

      axios
        .post(route(`maintenance.authenticate.password`), { password: this.password })
        .then((response) => {
            router.visit(route('welcome.page'));
          if (response.data.error) {
            this.errorMsg = response.data.error;
            this.showError = true;
          }
        })
        .catch((error) => {
          console.error(error);
          this.errorMsg = "An error occurred while processing the request.";
          this.showError = true;
        });
    },
  },
};

</script>
