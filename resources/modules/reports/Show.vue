<template>
    <div>
        <Tabular v-if="type === 'Tabular'" />
        <ProfitLoss v-else-if="type === 'ProfitLoss'" />
    </div>
</template>

<script setup>
import { computed, onBeforeMount } from "vue";
import { useRoute } from "vue-router";
import Tabular from "@modules@/reports/templates/Tabular.vue";
import ProfitLoss from "@modules@/reports/templates/ProfitLoss.vue";

const route = useRoute();

const type = computed(() => {
    switch (route.params.type) {
        case "summary-profit-loss":
        case "summary-balance-sheet":
            return "ProfitLoss";
        default:
            return "Tabular";
    }
});

onBeforeMount(() => {});
</script>

<style scoped></style>
