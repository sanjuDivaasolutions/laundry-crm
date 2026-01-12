<template>
    <IndexModule
        :index-store="moduleIndexStore"
        :form-store="moduleFormStore"
    />
</template>

<script setup>
import IndexModule from "@common@/components/IndexModule.vue";
import { useModuleIndexStore } from "@modules@/agents/agentsIndexStore";
import { useModuleFormStore } from "@modules@/agents/agentsFormStore";

const moduleIndexStore = useModuleIndexStore();
const moduleFormStore = useModuleFormStore();
</script>

<style scoped></style>
