<script lang="ts" setup>
import { usePaginator } from "momentum-paginator";

const props  = defineProps<{
    pagedata: Paginator<Array<any>>
}>();

const { previous, next, pages } = usePaginator(props.pagedata);
</script>

<template>
    <nav class="pagination is-centered" role="navigation" aria-label="pagination">
        <component is="Link" as="a" :href="previous.url" :class="{
            'pagination-previous is-hidden-mobile': previous.isActive,
            'pagination-previous is-hidden-mobile is-disabled': !previous.isActive,
        }">
            <i class="fa-regular fa-chevron-left"></i>
        </component>
        <component is="Link" as="a" :href="next.url" :class="{
            'pagination-next is-hidden-mobile': next.isActive,
            'pagination-next is-hidden-mobile is-disabled': !next.isActive,
        }">
            <i class="fa-regular fa-chevron-right"></i>
        </component>
        <ul class="pagination-list">
            <li v-for="page in pages">
                <component is="Link" as="button" :href="page.url" :class="{
                'pagination-link is-current': page.isCurrent,
                'pagination-link': page.isActive,
            }">
                    {{ page.label }}
                </component>
            </li>
        </ul>
    </nav>
</template>
