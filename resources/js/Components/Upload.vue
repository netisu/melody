<script setup lang="ts">
const props = defineProps<{
    modal: String,
}>()
</script>

<template>
    <div class="card card-body card-inner w-100"> 
        <input v-modal="props.modal" type="file" id="file" />
        <div class="align-center flex-container text-center">
            <div class="w-100">
                <div class="text-5xl mb-3 text-muted"><i class="fad fa-upload"
                        style="vertical-align: middle; margin-top: -2.5px;"></i>
                </div>
                <div class="text-md text-muted fw-semibold">Upload</div>
            </div>
        </div>
    </div>
</template>
<style scoped>
#file {
    width: inherit;
    height: inherit;
    background: rgba(150, 225, 150, 0.5);
    vertical-align: middle;
    text-align: center;
    padding: 0 20%;
    margin-top: inherit;
    color: #FFF;
    opacity: 0;
}
</style>