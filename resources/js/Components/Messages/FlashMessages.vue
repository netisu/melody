<script setup lang="ts">
import { ref, computed, watch } from "vue";

const props = defineProps<{
    flash?: {
        message: string;
        type: string;
    };
}>();

const show = ref(false); // Initially hide the toast
const message = computed(() => props.flash?.message);
const type = computed(() => props.flash?.type);
const showToastClass = ref(false);

const handleClick = () => {
    showToastClass.value = false;
    setTimeout(() => {
        show.value = false;
    }, 2000);
};

watch(
    () => props.flash,
    (newFlash) => {
        if (newFlash && newFlash.message && newFlash.type) {
            show.value = true;
            showToastClass.value = true;
            setTimeout(() => {
                showToastClass.value = false;
            }, 6000);
            setTimeout(() => {
                show.value = false;
            }, 7000);
        }
    },
    { deep: true },
);

const toastClass = computed(() => {
    switch (type.value) {
        case "success":
            return "alert-success";
        case "error":
            return "alert-danger";
        case "warning":
            return "alert-warning";
        case "info":
            return "alert-info";
        default:
            return "";
    }
});

const toastIcon = computed(() => {
    switch (type.value) {
        case "success":
            return "fad fa-check-circle";
        case "error":
            return "fad fa-circle-xmark";
        case "warning":
            return "fad fa-exclamation-circle";
        case "info":
            return "fad fa-info-circle";
        default:
            return "";
    }
});
</script>

<template>
    <nav
        v-if="message && show"
        class="toast-menu"
        :class="{
            'toast-menu-slide-in': showToastClass,
            'toast-menu-slide-out': !showToastClass,
        }"
        @click="handleClick"
    >
        <div
            :class="[
                'py-2',
                'mb-1',
                'text-center',
                'alert',
                'alert-toast',
                'fw-semibold',
                toastClass,
            ]"
        >
            <div class="gap-2 align-middle flex-container">
                <i :class="['text-lg', toastIcon]"></i>
                <div>{{ message }}</div>
            </div>
        </div>
    </nav>
</template>
