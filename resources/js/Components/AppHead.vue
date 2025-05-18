<script setup lang="ts">
import dayjs from "dayjs";
import { usePage, Head } from "@inertiajs/vue3";
// Props
defineProps({
    pageTitle: { type: String },
    description: { type: String },
    url: { type: String },
    cover: { type: String },
    item: { type: Boolean, default: false },
    iid: { type: Number },
    itime: { type: Number, default: null },
});
// Function to format the date using dayjs
const getFormattedDate = (timeStamp: number) => {
    // Check if timeStamp is not null and valid number
    if (timeStamp !== null && !isNaN(timeStamp)) {
        return dayjs(timeStamp).format("m-d-Y H:i");
    }
    return null; // Return an empty string if timeStamp is a null or an invalid number
};
</script>

<template>

    <Head>
        <title>{{ pageTitle }}</title>

        <meta :content="pageTitle" name="title" />
        <meta :content="description || 'A page on ' + usePage<any>().props.site.name + '.'" name="description" />

        <meta :content="url" property="og:url" />
        <meta content="website" property="og:type" />

        <meta :content="usePage<any>().props.site.name" property="og:site_name" />
        <meta :content="pageTitle" property="og:title" />
        <meta :content="description || 'A page on ' + usePage<any>().props.site.name + '.'" property="og:description" />
        <meta :content="cover || usePage<any>().props.site.icon" property="og:image" />

        <meta content="summary_large_image" property="twitter:card" />
        <meta :content="url" property="twitter:url" />
        <meta property="twitter:domain" :content="usePage<any>().props.site.production.domains.main">

        <meta :content="pageTitle" property="twitter:title" />
        <meta :content="description || 'A page on ' + usePage<any>().props.site.name + '.'"
            property="twitter:description" />
        <meta :content="cover || usePage<any>().props.site.icon" property="twitter:image" />
        <meta v-if="item == true" name="item-info" :data-id="iid" :data-onsale-until="getFormattedDate(itime)" />
    </Head>
</template>
