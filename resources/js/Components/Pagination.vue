<template>
    <nav class="flex items-center justify-between">
        <div class="flex-1 flex justify-between sm:hidden">
            <Link v-if="links[0].url" :href="links[0].url"
                  class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                Previous
            </Link>
            <Link v-if="links[links.length - 1].url" :href="links[links.length - 1].url"
                  class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                Next
            </Link>
        </div>
        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
            <div>
                <p class="text-sm text-gray-700">
                    Showing page {{ currentPage }} of {{ lastPage }}
                </p>
            </div>
            <div>
                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                    <Link v-for="link in links" :key="link.label" 
                          :href="link.url"
                          :class="getLinkClass(link)"
                          v-html="link.label">
                    </Link>
                </nav>
            </div>
        </div>
    </nav>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    links: Array,
});

const currentPage = computed(() => {
    const current = props.links.find(link => link.active);
    return current ? current.label : 1;
});

const lastPage = computed(() => {
    return props.links[props.links.length - 2]?.label || 1;
});

const getLinkClass = (link) => {
    if (link.active) {
        return 'z-10 bg-indigo-50 border-indigo-500 text-indigo-600 relative inline-flex items-center px-4 py-2 border text-sm font-medium';
    }
    if (!link.url) {
        return 'relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-500 cursor-default';
    }
    return 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50 relative inline-flex items-center px-4 py-2 border text-sm font-medium';
};
</script>