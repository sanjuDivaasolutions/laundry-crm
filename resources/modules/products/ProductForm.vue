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
  -  *  Last modified: 01/02/25, 7:08 pm
  -  *  Written by Chintan Bagdawala, 2025.
  -  */
  -->

<template>
    <card-container :hide-footer="true" :hide-header="true">
        <template #header>
            <div class="card-title">
                <h3 class="m-0 text-gray-800">{{ formModalTitle }}</h3>
            </div>
        </template>
        <template #body>
            <FormFields
                :fields="fields"
                :entry="entry"
                :mode="mode"
                @submit="onSubmit"
                @cancel="redirectIndex"
                :show-cancel="true"
                :defaults-route="defaultRoute"
                defaults-route-after-event="calculate-total"
            />
        </template>
    </card-container>
</template>

<script setup>
import FormFields from "@common@/components/FormFields.vue";
import { useModuleFormStore } from "@modules@/products/productsFormStore";
import { computed, onBeforeUnmount, onMounted, ref } from "vue";
import ApiService from "@/core/services/ApiService";
import { $catchResponse, $toastSuccess } from "@/core/helpers/utility";
import router from "@/router";
import { useRoute } from "vue-router";
import emitter from "@/core/plugins/mitt";

const props = defineProps({
    mode: {
        type: String,
        default: null,
    },
});

const route = useRoute();

const moduleFormStore = useModuleFormStore();
const module = computed(() => moduleFormStore.module);
const fields = computed(() => moduleFormStore.formFields);
const modals = computed(() => moduleFormStore.modals);
const entry = computed(() => moduleFormStore.entry);
const formModalTitle = ref("Invoice");

const defaultRoute = computed(() => {
    return `${moduleFormStore.route}/create`;
});

const onSubmit = (formEntry) => {
    switch (props.mode) {
        case "create":
            ApiService.post(moduleFormStore.route, formEntry)
                .then((response) => {
                    $toastSuccess("Product was created successfully");
                    redirectIndex();
                })
                .catch((error) => {
                    $catchResponse(error);
                });
            break;
        case "edit":
            ApiService.put(
                `${moduleFormStore.route}/${formEntry.id}`,
                formEntry
            )
                .then((response) => {
                    $toastSuccess("Product was updated successfully");
                    redirectIndex();
                })
                .catch((error) => {
                    $catchResponse(error);
                });
            break;
    }
};

const redirectIndex = () => {
    router.push({
        name: `${module.value.id}.index`,
    });
};

onBeforeUnmount(() => {
    moduleFormStore.resetEntry();
    emitter.off("onUnitChange");
    emitter.off("onUpdatePrices");
    emitter.off("onCompanyChange");
    emitter.off("show-form-modal-local");
    emitter.off("onOpeningStockChange");
});

onMounted(async () => {
    emitter.on("onUnitChange", moduleFormStore.onUnitChange);
    emitter.on("onUpdatePrices", moduleFormStore.onUpdatePrices);
    emitter.on("onCompanyChange", () => {
        emitter.emit("refresh-options", { endpoint: "company-categories" });
    });
    emitter.on("show-form-modal-local", (payload) => {
        setTimeout(() => {
            emitter.emit("refresh-options", { endpoint: "shelves" });
        }, 200);
    });
    emitter.on("onOpeningStockChange", (payload) => {
        //Update opening stock total
        moduleFormStore.updateOpeningStockValues();
    });
    if (props.mode === "edit") {
        const route = useRoute();
        const id = route.params.id;
        formModalTitle.value = modals.value.form.editTitle;
        await moduleFormStore.loadEditData(id);
        console.log("Edit data loaded");
        emitter.emit("onCompanyChange");
    } else {
        formModalTitle.value = modals.value.form.createTitle;
        moduleFormStore.resetEntry();
    }
});
</script>
