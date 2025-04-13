<script lang="ts" setup>
import { usePaginator } from "momentum-paginator";

const props  = defineProps<{
    pagedata: Paginator<Array<any>>
}>();

const { previous, next, pages } = usePaginator(props.pagedata);
</script>

<template>
  <div class="section">
      <ul class="pagination flex-container align-center">
    <component
    is="Link"
    as="button"
      :href="previous.url"
      :class="{
        'page-link': previous.isActive,
        'page-link page-disabled': !previous.isActive,
      }"
    >
     <i class="fa-regular fa-chevron-left"></i>
    </component>

    <component
      v-for="page in pages"
      is="Link"
      as="button"
      :href="page.url"
      :class="{
        'page-link page-active': page.isCurrent,
        'page-link': page.isActive,
      }"
    >
      {{ page.label }}
    </component>

    <component
      is="Link"
      as="button"
      :href="next.url"
      :class="{
        'page-link': next.isActive,
        'page-link page-disabled': !next.isActive,
      }"
    >
      <i class="fa-regular fa-chevron-right"></i>
    </component>
          </ul>
  </div>
</template>
