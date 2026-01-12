<template>
    <ShowValues :fields="fields" :entry="entry" />
</template>

<script setup>
import ShowValues from "@common@/components/show/ShowValues.vue";

const props = defineProps({
    entry: {
        type: Object,
        required: true,
    },
    fields: {
        type: Array,
        required: true,
    },
});
</script>

<style scoped></style>
