<template>
    <div class="text-end">{{ value }}</div>
</template>

<script setup>
import { computed } from "vue";

const props = defineProps({
    modelValue: {
        required: true,
    },
    fixed: {
        type: Number,
        default: 2,
    },
});

const value = computed(() => {
    return Number(props.modelValue).toFixed(props.fixed);
});
</script>

<style scoped></style>
