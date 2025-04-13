<template>
    <nav aria-label="Page navigation">
        <ul class="pagination justify-content-center">
            <li class="page-item" :class="{ disabled: currentPage === 1 }">
                <a class="page-link" @click="changePage(1)" aria-label="First">
                    <span aria-hidden="true">&laquo;&laquo;</span>
                </a>
            </li>
            <li class="page-item" :class="{ disabled: currentPage === 1 }">
                <a class="page-link" @click="prevPage" aria-label="Previous">
                    <span aria-hidden="true">&laquo;</span>
                </a>
            </li>
            <li class="page-item" v-for="page in pages" :key="page" :class="{ active: currentPage === page }">
                <a class="page-link" @click="changePage(page)">{{ page }}</a>
            </li>
            <li class="page-item" :class="{ disabled: currentPage === totalPages }">
                <a class="page-link" @click="nextPage" aria-label="Next">
                    <span aria-hidden="true">&raquo;</span>
                </a>
            </li>
            <li class="page-item" :class="{ disabled: currentPage === totalPages }">
                <a class="page-link" @click="changePage(totalPages)" aria-label="Last">
                    <span aria-hidden="true">&raquo;&raquo;</span>
                </a>
            </li>
        </ul>
    </nav>
</template>
  
<script lang="ts">
export default {
    props: {
        currentPage: Number,
        totalPages: Number,
    },
    computed: {
        pages() {
            const start = Math.max(1, this.currentPage - 2);
            const end = Math.min(this.totalPages, this.currentPage + 2);
            const pageNumbers = [];
            for (let i = start; i <= end; i++) {
                pageNumbers.push(i);
            }
            return pageNumbers;
        },
    },
    methods: {
        changePage(page) {
            if (this.currentPage !== page) {
                this.$emit('page-change', page);
            }
        },
        prevPage() {
            this.changePage(this.currentPage - 1);
        },
        nextPage() {
            this.changePage(this.currentPage + 1);
        },
    },
};
</script>
  