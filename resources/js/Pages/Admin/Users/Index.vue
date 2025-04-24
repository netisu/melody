<script setup lang="ts">
import { route } from "momentum-trail"

import { usePage } from "@inertiajs/vue3";
import Navbar from "@/Components/LayoutParts/Navbar.vue";
import AppHead from "@/Components/AppHead.vue";
import Pagination from "@/Components/Pagination.vue";
import Sidebar from "@/Components/LayoutParts/Sidebar.vue";
import AdminNav from "@/Components/LayoutParts/Admin/AdminNav.vue";

</script>

<template>
  <AppHead pageTitle="User Index" description="Login to" :url="route('auth.login.page')" />
  <Navbar />
  <Sidebar>
    <div class="cell medium-3">
            <AdminNav />
        </div>
        <div class="cell medium-9">
    <div class="card">
      <div class="card-content">
        <div class="columns">
          <div class="column is-3">
            <div class="select title is-6 is-fullwidth">
              <select>
                <option>Username</option>
                <option>Email</option>
              </select>
            </div>
          </div>
          <div class="column is-7">
            <input class="input" type="Search" placeholder="Search" />
          </div>
          <div class="column">
            <a class="button is-fullwidth" href="">Search</a>
          </div>
        </div>
      </div>
    </div>

    <br />
    <div class="card">
      <div class="card-content">
        <p class="title is-4">Users:</p>
        <br />
        <article class="media" v-for="(user, index) in usePage<any>().props.users.data" :key="index">
          <figure class="media-left">
            <p class="image is-128x128">
              <img class="is-rounded" :src="user.headshot" style="border: 2px solid #616161 !important" />
            </p>
          </figure>
          <div class="media-content">
            <div class="content">
              <br />
              <div class="title is-4">
                <span class="mb-4">{{ user.display_name }} <span class="has-text-grey">({{ "@" + user.username
                    }})</span></span>
                <div class="mt-4 tags">
                  <span v-if="user.isBanned" class="tag is-danger">This user is banned</span>
                  <span v-if="user.isStaff" class="tag is-info">{{ user.position }}</span>
                </div>
              </div>
              <p class="subtitle is-6">{{ user.email }}</p>
              <div class="buttons">

                <Link class="button" :href="route(`admin.users.manage-user`, { id: user.id })">Manage User</Link>
                <a class="button is-disabled is-" :href="route(`user.profile`, { username: user.username })">Go to
                  Profile</a>
              </div>
            </div>
          </div>
          <div class="media-right"></div>
        </article>
        <Pagination :pagedata="usePage<any>().props.users"></Pagination>
        <div v-if="!usePage<any>().props.users.data.length">
          ERR T100: No users found
        </div>
      </div>
    </div>
  </div>
</Sidebar>
</template>
