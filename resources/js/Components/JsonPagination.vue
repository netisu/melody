<template>
    <nav class="pagination is-centered">
      <button
        class="pagination-previous is-hidden-mobile"
        @click.prevent="emitPageClick(pagedata?.current_page - 1)"
        :disabled="pagedata?.current_page <= 1"
      >
        <i class="fa fa-angle-double-left" aria-hidden="true"></i>
      </button>
  
      <button
        class="pagination-next is-hidden-mobile"
        @click.prevent="emitPageClick(pagedata?.current_page + 1)"
        :disabled="pagedata?.current_page >= pagedata?.last_page"
      >
        <i class="fa fa-angle-double-right" aria-hidden="true"></i>
      </button>
  
      <ul class="pagination-list">
        <li v-for="page in pagedata?.last_page" :key="page">
          <button
            class="pagination-link"
            :class="{ 'is-current': pagedata?.current_page === page }"
            @click.prevent="emitPageClick(page)"
          >
            {{ page }}
          </button>
        </li>
      </ul>
    </nav>
  </template>
  
  <script lang="ts">
  import { defineComponent } from 'vue';
  
  interface Pagedata {
    current_page: number;
    last_page: number;
    total: number;
  }
  
  export default defineComponent({
    props: {
      pagedata: {
        type: Object as () => Pagedata | undefined,
        required: true,
        default: () => ({
          current_page: 0,
          last_page: 0,
          total: 0,
        }),
      },
    },
    emits: ['page-clicked'],
    methods: {
      emitPageClick(page: number) {
        this.$emit('page-clicked', page);
      },
    },
  });
  </script>