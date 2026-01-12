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
  -  *  Last modified: 21/01/25, 10:29 am
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
            />
        </template>
    </card-container>
</template>

<script setup>
import FormFields from "@common@/components/FormFields.vue";
import { useModuleFormStore } from "@modules@/quotations/quotationsFormStore";
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

const onSubmit = (formEntry) => {
    switch (props.mode) {
        case "create":
            ApiService.post(moduleFormStore.route, formEntry)
                .then((response) => {
                    $toastSuccess("Sales invoice created successfully");
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
                    $toastSuccess("Sales invoice updated successfully");
                    redirectIndex();
                })
                .catch((error) => {
                    $catchResponse(error);
                });
            break;
    }
};

const calculateTotal = () => {
    let subTotal = 0;
    entry.value.items.forEach((item) => {
        if (!item.quantity || !item.rate) return;
        const total = item.quantity * item.rate;
        item.amount = total;
        subTotal += total;
    });

    //calculate 5% tax
    const taxTotal = subTotal * 0.05;
    const grandTotal = Number(subTotal) + Number(taxTotal);

    entry.value.sub_total = Number(subTotal).toFixed(2);
    entry.value.tax_total = Number(taxTotal).toFixed(2);
    entry.value.grand_total = Number(grandTotal).toFixed(2);
};

const redirectIndex = () => {
    router.push({
        name: `${module.value.id}.index`,
    });
};

const defaultRoute = computed(() => {
    let r = `${moduleFormStore.route}/create`;
    const type = route.params.type;
    if (type === "po") {
        r += `?poId=${route.params.id}`;
    }
    if (type === "clone") {
        r += `?cloneId=${route.params.id}`;
    }
    return r;
});

onBeforeUnmount(() => {
    moduleFormStore.resetEntry();
    emitter.off("calculate-total");
    emitter.off("onSalesOrderChange");
    emitter.off("onProductChange");
});

onMounted(() => {
    if (props.mode === "edit") {
        const route = useRoute();
        const id = route.params.id;
        formModalTitle.value = modals.value.form.editTitle;
        moduleFormStore.loadEditData(id);
    } else {
        formModalTitle.value = modals.value.form.createTitle;
        moduleFormStore.resetEntry();
    }
    emitter.on("calculate-total", calculateTotal);
    emitter.on("onSalesOrderChange", moduleFormStore.onSalesOrderChange);
    emitter.on("onProductChange", moduleFormStore.onProductChange);
});
</script>
