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
<template>
  <AppHead pageTitle="Login" :description="'Login to ' + usePage<any>().props.site.name + ''"
    :url="route('auth.login.page')" />
  <Navbar />
  <Sidebar>
    <FlashMessages />
    <div class="cell medium-4">
      <div class="mb-2">
        <div class="text-2xl fw-semibold">Log In</div>
        <div class="text-sm text-muted fw-semibold">
          Don't have an account?
          <a href="#" class="d-inline-block squish">Sign Up</a>
        </div>
      </div>
      <div></div>
      <div class="card card-body">
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
    </div>
  </Sidebar>
  <Footer />
</template>
