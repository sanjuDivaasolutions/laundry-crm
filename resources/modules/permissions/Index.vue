<template>
    <IndexModule
        :index-store="moduleIndexStore"
        :form-store="moduleFormStore"
    />
</template>

<script setup>
import IndexModule from "@common@/components/IndexModule.vue";
import { useModuleIndexStore } from "@modules@/permissions/permissionsIndexStore";
import { useModuleFormStore } from "@modules@/permissions/permissionsFormStore";

const moduleIndexStore = useModuleIndexStore();
const moduleFormStore = useModuleFormStore();
</script>

<style scoped></style>
