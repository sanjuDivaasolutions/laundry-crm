<template>
    <ReportListSingle :entry="entry" />
</template>

<script setup>
import ReportListSingle from "@modules@/reports/components/ReportListSingle.vue";

const props = defineProps({
    entry: {
        type: Object,
        required: true,
    },
});
</script>

<style scoped></style>
