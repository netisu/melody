<script setup lang="ts">
import { usePage } from "@inertiajs/vue3";
import { route } from 'ziggy-js';

const { props } = usePage<any>();
</script>
<template>
    <div class="mb-2 text-xl fw-semibold">{{ props.site.name }} Administration</div>
    <ul class="tabs flex-dir-column">
        <li class="tab-item">
            <Link :href="route(`admin.page`)" class="tab-link squish"
                :class="{ 'active': route().current(`admin.page`) }">
            <i class="fas fa-home"></i>
            Dashboard
            </Link>
        </li>
        <li class="tab-item">
            <Link :href="route(`admin.users.page`)" class="tab-link squish"
                :class="{ 'active': route().current(`admin.users.*`) }">
            <i class="fas fa-user"></i>
            User Search
            </Link>
        </li>
        <li class="tab-item">
            <Link :href="route(`admin.items.page`)" class="tab-link squish"
                :class="{ 'active': route().current(`admin.items.*`) }">
            <i class="fas fa-tshirt"></i>
            Item Search
            </Link>
        </li>
        <li class="tab-item">
            <a href="#" class="tab-link squish">
                <i class="fas fa-notes"></i>
                Pending Assets
                <div class="badge badge-info" style="float: right">
                    1
                </div>
            </a>
        </li>
    </ul>
</template>
