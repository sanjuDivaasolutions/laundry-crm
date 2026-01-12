<template>
    <IndexModule
        :index-store="moduleIndexStore"
        :form-store="moduleFormStore"
    />
</template>

<script setup>
import IndexModule from "@common@/components/IndexModule.vue";
import { useModuleIndexStore } from "@modules@/shelves/shelvesIndexStore";
import { useModuleFormStore } from "@modules@/shelves/shelvesFormStore";

const moduleIndexStore = useModuleIndexStore();
const moduleFormStore = useModuleFormStore();
</script>

<style scoped></style>
