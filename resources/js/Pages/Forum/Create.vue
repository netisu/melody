<script lang="ts" setup>
import Navbar from '@/Components/LayoutParts/Navbar.vue';
import Sidebar from '@/Components/LayoutParts/Sidebar.vue';
import Footer from '@/Components/LayoutParts/Footer.vue';
import { useForm } from '@inertiajs/vue3';
import { route } from "momentum-trail"

import axios from 'axios';

const props = defineProps({
  topic: {
    type: Object,
    required: true,
  },
});

const form = useForm({
  title: '',
  body: '',
});

const submit = () => {
  axios.get(`/sanctum/csrf-cookie`).then(() => {
    form.post(route(`forum.create.validate`, { id: props.topic?.['id'] }), {
      onFinish: () => form.reset('body'),
    });
  });
};
const addText = (text) => {
  form.body += text;
};
</script>
<template>
    <Navbar />
    <Sidebar>
        <div class="mb-4 cell medium-8">
            <Link :href="route(`forum.page`, { id: topic.id })">
                <i class="fad fa-arrow-left"></i>
                Back to Discuss
            </Link>

            <div class="text-xl text-body fw-semibold bg-primary">Create a Post in {{ topic.name }}</div>
            <div class="text-xs text-muted bg-primary">
                Make sure you post in the appropriate sub-forum and that your post does not break any site rules.
            </div>
            <div class="mx-1 my-2 divider"></div>
            <form @submit.prevent="submit">
                <label for="post-title" class="mb-2 text-xs fw-bold text-uppercase">Post Title</label>
                <input id="post-title" class="mb-2 form-has-section-color  form" type="text" name="title"
                    v-model="form.title" placeholder="Title" maxlength="64">


                <label for="post-body" class="mb-2 text-xs fw-bold text-uppercase">Post Content</label>
                <div class="position-relative"><textarea id="post-body" name="body" v-model="form.body"
                        class="mb-2 form form-has-button form-has-section-color pe-5" maxlength="4096" rows="5"
                        placeholder="Post your thoughts here."></textarea>
                    <input type="submit" :disabled="form.processing" class="btn btn-success btn-sm has-ripple"
                        value="Post" style="position: absolute; bottom: 10px; right: 10px;">
                </div>

            </form>
        </div>
        <div class="cell medium-4">
            <div class="gap-2 mt-3 grid-x">
                <button class="btn btn-info" @click="addText('[color=#hexcodehere]Colored Text[/color]')">Add
                    Color</button>
                <button class="btn btn-warning" @click="addText('[b]Bold Text[/b]')">Bold Text</button>
                <button class="btn btn-success" @click="addText('[i]Italic Text[/i]')">Italic Text</button>
                <button class="btn btn-success" @click="addText('[u]Underlined Text[/u]')">Underline Text</button>
                <button class="btn btn-info" @click="addText('[img]Image Link[/img]')">Insert an Image</button>
                <button class="btn btn-danger" @click="addText('[url=https://yourlinkhere]Link Text[/url]')">Link To
                    Website</button>
            </div>
        </div>
    </Sidebar>
    <Footer />
</template>
