<script setup lang="ts">
import { route } from 'momentum-trail';
import VLazyImage from "v-lazy-image";

// Props
defineProps<{
    name?: string,
    DisplayName?: string,
    date?: string,
    message?: string,
    Thumbnail?: string,

}>();
</script>
<template>
    <div class="gap-3 section flex-container flex-dir-column-sm">
        <div class="mx-auto flex-child-grow" style="width: 100px">
            <Link :href="route(`user.profile`, { username: name})" class="text-sm text-center d-block squish">
                <v-lazy-image :src="Thumbnail" width="60" class="headshot" alt="Avatar"
                    src-placeholder="/assets/img/dummy_headshot.png" />
                <div style="line-height: 16px">
                    <div class="text-truncate">{{ DisplayName }}</div>
                    <div class="text-xs text-muted text-truncate">
                        {{ '@' + name }}
                    </div>
                </div>
            </Link>
        </div>
        <div class="card card-body card-inner w-100">
            <div class="align-middle flex-container align-justify">
                <div class="w-100">
                    <div class="text-xs text-muted">
                        <i class="far fa-clock me-1" style="vertical-align: middle;margin-top: -2.5px;font-size: 10px;"></i>
                        Posted {{ date }}
                    </div>
                    <div>
                        {{ message }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
