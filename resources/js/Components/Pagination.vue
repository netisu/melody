<script setup lang="ts">
import { Link } from "@inertiajs/vue3";
import { computed } from 'vue';

const props = defineProps({
  pagedata: Object
});

const filteredLinks = computed(() => {
  if (!props.pagedata || !props.pagedata.links) {
    return [];
  }

  return props.pagedata.links.filter(link => {
    return link.label !== '&laquo; Previous' && link.label !== 'Next &raquo;';
  });
});
</script>

<template>
  <div class="section">
    <ul class="pagination flex-container align-center">
      <Link as="button" :href="props.pagedata?.['prev_page_url'] || ''" :class="{
        'page-link': props.pagedata?.['prev_page_url'],
        'page-link page-disabled': !props.pagedata?.['prev_page_url'],
      }">
      <i class="fa-regular fa-chevron-left"></i>
      </Link>
        <Link v-for="(page, key) in filteredLinks" :key="key"
          as="button" :href="page?.url || ''" :class="{
            'page-link page-active': page?.active,
            'page-link': !page?.active,
          }">
        {{ page?.label }}
        </Link>
      <Link as="button" :href="props.pagedata?.['next_page_url'] || ''" :class="{
        'page-link': props.pagedata?.['next_page_url'],
        'page-link page-disabled': !props.pagedata?.['next_page_url'],
      }">
      <i class="fa-regular fa-chevron-right"></i>
      </Link>
    </ul>
  </div>
</template>
