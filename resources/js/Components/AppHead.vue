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

        <meta charset="UTF-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="theme-color" content="#03a9f4" />
        <meta http-equiv="Content-Type" content="application/x-html+html; charset=UTF-8" />
        <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests" />

        <meta name="robots" content="index,follow" />
        <link rel="shortcut icon" type="image/x-icon" :href="usePage<any>().props.site.icon" />
        <meta name="msapplication-TileColor" content="#03a9f4" />
        <meta name="robots" content="index,follow" />

        <!-- Scripts -->
        <meta name="msapplication-config" content="/browserconfig.ae3u4gn3.xml" />
        <link rel="manifest" href="/Aeow2mf3.webmanifest" crossorigin="use-credentials" />

        <!-- Fonts -->
        <link rel="stylesheet" href="https://kit-pro.fontawesome.com/releases/v6.1.2/css/pro.min.css" />
        <meta :content="pageTitle" name="title" />
        <meta :content="description || 'A page on ' + usePage<any>().props.site.name + '.'" name="description" />

        <meta :content="url" property="og:url" />
        <meta content="website" property="og:type" />
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
