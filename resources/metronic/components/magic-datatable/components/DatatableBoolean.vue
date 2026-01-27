<template>
    <div class="d-flex justify-content-center">
        <span :class="badgeClass">{{ label }}</span>
    </div>
</template>

<script setup>
import { computed } from "vue";

const props = defineProps({
    value: {
        type: [Boolean, Number, String],
        default: false,
    },
});

const isTrue = computed(() => {
    return props.value === true || props.value === 1 || props.value === "1";
});

const label = computed(() => (isTrue.value ? "Active" : "Inactive"));
const badgeClass = computed(() =>
    isTrue.value ? "badge badge-light-success" : "badge badge-light-danger"
);
</script>
