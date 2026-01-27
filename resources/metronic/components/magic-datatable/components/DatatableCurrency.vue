<template>
    <div class="text-end w-100 pe-3">
        {{ formattedValue }}
    </div>
</template>

<script setup>
import { computed } from "vue";
import { $moneyfy } from "@/core/helpers/utility";

const props = defineProps({
    value: {
        type: [Number, String],
        default: 0,
    },
});

const formattedValue = computed(() => {
    return $moneyfy(props.value, "USD"); // Default to USD, can be improved to use row currency if available
});
</script>
