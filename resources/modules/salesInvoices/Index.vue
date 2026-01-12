<template>
    <IndexModule
        :index-store="moduleIndexStore"
        :form-store="moduleFormStore"
    />
</template>

<script setup>
import IndexModule from "@common@/components/IndexModule.vue";
import { useModuleIndexStore } from "@modules@/salesInvoices/salesInvoicesIndexStore";
import { useModuleFormStore } from "@modules@/salesInvoices/salesInvoicesFormStore";

const moduleIndexStore = useModuleIndexStore();
const moduleFormStore = useModuleFormStore();
</script>

<style scoped></style>
