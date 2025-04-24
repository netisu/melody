<script setup lang="ts">
import Navbar from "@/Components/LayoutParts/Navbar.vue";
import { useForm, usePage } from "@inertiajs/vue3";
import { ref } from "vue";
import { route } from "momentum-trail"

import FlashMessages from "@/Components/Messages/FlashMessages.vue";
import AppHead from "@/Components/AppHead.vue";
import Sidebar from "@/Components/LayoutParts/Sidebar.vue";
import axios from "axios";
import Footer from "@/Components/LayoutParts/Footer.vue";
import VLazyImage from 'v-lazy-image';
const ConfirmingAuth = ref(false);

const form = useForm({
  username: "",
  password: "",
  remember: false,
});

const submit = () => {
  axios.get(`/sanctum/csrf-cookie`).then(() => {
    form.post(route(`auth.login.validate`), {
      onFinish: () => form.reset("password"),
    });
  });
};

const ConfirmUserAuth = () => {
  ConfirmingAuth.value = true;
};
</script>
<style scoped>
.highlight:before {
  content: "";
  width: 100%;
  height: 100%;
  display: block;
  position: absolute;
  left: 0;
  top: 0;
  /* background-image: linear-gradient(to top, #add8e611, #fffb); */
  background-image: linear-gradient(to top, #add8e6, #fff);
  opacity: 0.3;
  box-shadow: 0px 0px 10px #000;
  z-index: -1;
}
</style>
<template>
  <AppHead pageTitle="Login" :description="'Login to ' + usePage<any>().props.site.name + ''"
    :url="route('auth.login.page')" />
  <Navbar />
  <Sidebar>
    <FlashMessages />
    <div class="cell large-4 medium-6 small-12">
      <div class="align-middle flex-container align-center">
        <div class="gap-2 mb-4 text-center align-center">

        <v-lazy-image src="/assets/img/dummy_headshot.png" class="space-image mb-2" width="150" alt="Headshot"
          src-placeholder="/assets/img/dummy_headshot.png" />
        <div class="text-center text-2xl fw-semibold" style="line-height: 16px">
          <span> {{ form.username }} </span>
        </div>
      </div>
      </div>
      <form @submit.prevent="submit">
        <div class="mb-2">
          <div :class="{ 'text-danger': form.errors.username }" class="text-xs fw-bold text-muted text-uppercase">
            Username
          </div>
          <input type="text" v-model="form.username" class="form" placeholder="Username..." />
          <div v-if="form.errors.username" class="text-xs text-danger fw-semibold">
            {{ form.errors.username }}
          </div>
        </div>
        <div class="mb-2">
          <div :class="{ 'text-danger': form.errors.password }" class="text-xs fw-bold text-muted text-uppercase">
            Password
          </div>
          <input type="password" v-model="form.password" class="mb-2 form" placeholder="Password..." />
          <div v-if="form.errors.password" class="text-xs text-danger fw-semibold">
            {{ form.errors.password }}
          </div>
        </div>
        <div class="mx-1 my-3 divider"></div>

        <div class="align-middle flex-container align-justify">
          <input type="submit" class="btn btn-success" v-bind:class="{ 'is-loading': ConfirmingAuth }"
            :disabled="form.processing" @click="ConfirmUserAuth" value="Log In" />
          <Link :href="route('auth.forgot.page')" class="text-sm fw-semibold squish">Forgot Password?</Link>
        </div>
      </form>
      <div class="gap-2 mx-1 my-3 mb-3 flex-container-lg">
        <button class="mb-2 btn btn-gray btn-block mb-lg-0">
          <img src="/assets/img/google.png" width="20" alt="google_logo" class="me-1" style="
                        margin-top: -3px;
                        filter: drop-shadow(
                            0 1px 1px
                                rgb(0, 0, 0, 0.2)
                        );
                    " />
        </button>
        <button class="mb-2 btn btn-discord btn-block mb-lg-0">
          <i class="fab fa-discord me-1"></i>
        </button>
        <button class="btn btn-secondary btn-block">
          <i class="fab fa-github me-1"></i>
        </button>
      </div>
    </div>
  </Sidebar>
  <Footer />
</template>
