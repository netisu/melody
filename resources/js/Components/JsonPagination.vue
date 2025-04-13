<template>
    <nav class="pagination is-centered">
        <Link class="pagination-previous is-hidden-mobile" :href="getPageClick(pagedata?.['current_page'] - 1)"
            v-show="pagedata?.['current_page'] > 1">
        <i class="fa fa-angle-double-left" aria-hidden="true"></i>
        </Link>

        <Link class="pagination-next is-hidden-mobile" :href="getPageClick(pagedata?.['current_page'] + 1)"
            v-show="pagedata?.['current_page'] < pagedata?.['last_page']">
        <i class="fa fa-angle-double-right" aria-hidden="true"></i>
        </Link>

        <ul class="pagination-list">
            <li v-for="page in pagedata?.['last_page']">
                <Link class="pagination-link" v-bind:class="{ 'is-current': (pagedata?.['current_page'] == page) }"
                    :href="getPageClick(page)">
                {{ page }}
                </Link>
            </li>
        </ul>
    </nav>
</template>
<script lang="ts">
export default {
    props: {
        pagedata: {
            type: Object,
            required: true,
            default: () => ({
                current_page: 0,
                last_page: 0,
                total: 0
            })
        }
    },
    methods: {
        getPageClick(page) {
            this.$emit('page-clicked', page);
        }
    }
}
</script>