<template>
    <IndexModule
        :index-store="moduleIndexStore"
        :form-store="moduleFormStore"
    />
</template>

<script setup>
import IndexModule from "@common@/components/IndexModule.vue";
import { useModuleIndexStore } from "@modules@/paymentTerms/paymentTermsIndexStore";
import { useModuleFormStore } from "@modules@/paymentTerms/paymentTermsFormStore";

const moduleIndexStore = useModuleIndexStore();
const moduleFormStore = useModuleFormStore();
</script>

<style scoped></style>
