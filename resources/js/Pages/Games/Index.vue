<script setup lang="ts">
import Navbar from '@/Components/LayoutParts/Navbar.vue';
import Sidebar from '@/Components/LayoutParts/Sidebar.vue';
import Footer from '@/Components/LayoutParts/Footer.vue';
import { usePage } from '@inertiajs/vue3';

</script>

<template>
    <Navbar />
    <Sidebar>
        <div class="cell large-10 medium-12" id="mainframe">
            <div class="card card-body">
                <div class="text-xl text-info">Games are coming soon</div>
                <div class="divider"></div>
                <p>
                    Games are currently a work in progress. Our development team is working constantly to bring you a
                    stable game client. If you have any questions or concerns, you can email our support team over at <a
                        :href="'mailto:' + usePage<any>().props.site?.production?.domains?.support">{{ usePage<any>
                            ().props.site?.production?.domains?.support }}</a>
                </p>
            </div>
        </div>
    </Sidebar>
    <Footer />
</template>
