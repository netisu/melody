<script setup lang="ts">
import Navbar from "@/Components/LayoutParts/Admin/AdminNav.vue";
import AppHead from "@/Components/AppHead.vue";
defineProps<{
    permission: string,
}>();

</script>

<template>
    <AppHead pageTitle="Access Denied" :description="'You dont have the **' + permission + '** permission.'" />
    <Navbar>
        <div class="columns">
            <div class="column is-3"></div>
            <div class="column">
                <div class="card">
                    <div class="card-content">
                        <span class="is-size-1"><i class="fad fa-warning"></i></span>

                        <span class="is-size-1">Oops!</span>
                        <p>You don't have the
                            <span class="has-text-weight-semibold">**{{ permission }}**</span> permission.
                        </p>
                        <p>Please ask a Higher-up for help.</p>
                    </div>
                </div>
            </div>
            <div class="column is-3"></div>
        </div>
    </Navbar>
</template>
