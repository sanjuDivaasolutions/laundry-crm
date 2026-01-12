<!--
  - /*
  -  *  Copyright (c) 2025 Divaa Solutions. All rights reserved.
  -  *
  -  *  This software is the confidential and proprietary information of Divaa Solutions
  -  *  ("Confidential Information"). You shall not disclose such Confidential Information and
  -  *  shall use it only in accordance with the terms of the license agreement you entered into
  -  *  with Divaa Solutions.
  -  *
  -  *  Unauthorized copying of this file, via any medium is strictly prohibited.
  -  *  Proprietary and confidential.
  -  *
  -  *  Last modified: 05/02/25, 6:02 pm
  -  *  Written by Chintan Bagdawala, 2025.
  -  */
  -->

<template>
    <IndexModule
        :index-store="moduleIndexStore"
        :form-store="moduleFormStore"
    />
</template>

<script setup>
import IndexModule from "@common@/components/IndexModule.vue";
import { useModuleIndexStore } from "@modules@/inventoryAdjustments/inventoryAdjustmentsIndexStore";
import { useModuleFormStore } from "@modules@/inventoryAdjustments/inventoryAdjustmentsFormStore";
import { onBeforeUnmount, onMounted } from "vue";
import emitter from "@/core/plugins/mitt";

const moduleIndexStore = useModuleIndexStore();
const moduleFormStore = useModuleFormStore();

onMounted(() => {
    emitter.on("onProductChange", () => {
        emitter.emit("refresh-options", { endpoint: "active-product-shelves" });
    });
});

onBeforeUnmount(() => {
    emitter.off("onProductChange");
});
</script>

<style scoped></style>
