<script setup lang="ts">
  import Navbar from "@/Components/LayoutParts/Navbar.vue";
  import Sidebar from "@/Components/LayoutParts/Sidebar.vue";
  import Footer from "@/Components/LayoutParts/Footer.vue";
  import { usePage } from "@inertiajs/vue3";

  function typeIcon(type: string): string | null {
    const iconMap = {
        user: "fad fa-user",
        gift: "fad fa-gift",

      // Add more icon mappings here
    };

    return iconMap[type] || null;
  }
</script>

<template>
  <Navbar />
  <Sidebar>
    <div class="cell large-10 medium-12">
      <div class="mb-2 align-middle flex-container align-justify">
        <div class="text-xl fw-semibold">Notifications</div>
        <a href="#" class="btn btn-success btn-sm"
          ><i class="fas fa-envelope me-2"></i>Read All</a
        >
      </div>
      <div class="card card-body">
        <div
          v-if="!usePage<any>().props.auth?.user?.notifications.length"
          class="text-center dropdown-item"
        >
          <div class="gap-3 flex-container flex-dir-column">
            <i class="text-5xl fad fa-face-sleeping text-muted"></i>
            <div style="line-height: 16px">
              <div class="text-xs fw-bold text-muted text-uppercase">
                No Notifications
              </div>
              <div class="text-xs text-muted fw-semibold">
                You have not recieved any notifications recently.
              </div>
            </div>
          </div>
        </div>
        <div
          v-else
          v-for="notification in usePage<any>().props.auth?.user?.notifications"
          class="dropdown-item"
        >
          <Link :href="notification.data.route" class="dropdown-link">
            <div class="gap-1 align-middle flex-container">
              <i
                class="text-lg text-center fas fa-comments-alt flex-child-grow"
                style="width: 38px"
                :class="typeIcon(notification.data.type)"
              ></i>
              <div class="gap-2 align-middle flex-container w-100">
                <div class="min-w-0" style="line-height: 16px">
                  <div class="text-sm text-truncate">
                    <span class="search-keyword" v-if="notification.data.object"
                      >{{ notification.data.object }} &nbsp;</span
                    >
                    <span class="text-sm text-muted">{{
                      notification.data.message
                    }}</span>
                  </div>
                  <div class="text-xs text-muted">
                    {{ notification.DateHum }}
                  </div>
                </div>
              </div>
            </div>
          </Link>
        </div>
        <div
          :class="{ 'mx-1 my-3 divider': usePage<any>().props.auth?.user?.notifications?.data > 0 }"
        ></div>
      </div>
    </div>
  </Sidebar>
  <Footer />
</template>
