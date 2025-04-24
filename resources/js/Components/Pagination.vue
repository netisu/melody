<script setup lang="ts">
import { usePaginator } from "momentum-paginator";
import { Link } from "@inertiajs/vue3";

interface Props {
  pagedata: Paginator<any[]>;
}

const props = defineProps<Props>();

const { previous, next, pages } = usePaginator(props.pagedata);
</script>

<template>
  <div class="section">
    <ul class="pagination flex-container align-center">
      <Link
        as="button"
        :href="previous?.url || ''"
        :class="{
          'page-link': previous?.isActive,
          'page-link page-disabled': !previous?.isActive,
        }"
      >
        <i class="fa-regular fa-chevron-left"></i>
      </Link>

      <Link
        v-for="page in pages"
        :key="page.label"
        as="button"
        :href="page.url"
        :class="{
          'page-link page-active': page.isCurrent,
          'page-link': page.isActive,
        }"
      >
        {{ page.label }}
      </Link>

      <Link
        as="button"
        :href="next?.url || ''"
        :class="{
          'page-link': next?.isActive,
          'page-link page-disabled': !next?.isActive,
        }"
      >
        <i class="fa-regular fa-chevron-right"></i>
      </Link>
    </ul>
  </div>
</template>