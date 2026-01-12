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
  -  *  Last modified: 15/01/25, 11:18 am
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
            <!-- Barcode Quick Add -->
            <BarcodeQuickAdd
                :entry="entry"
                items-field="items"
                warehouse-field="warehouse"
                @item-added="onItemAdded"
            />

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
import { useModuleFormStore } from "@modules@/salesInvoices/salesInvoicesFormStore";
import { computed, onBeforeUnmount, onMounted, ref, watch } from "vue";
import ApiService from "@/core/services/ApiService";
import { $catchResponse, $toastSuccess } from "@/core/helpers/utility";
import router from "@/router";
import { useRoute } from "vue-router";
import emitter from "@/core/plugins/mitt";
import { useTaxStore } from "@common@/components/taxStore";
import { useTotals } from "@common@/composables/useTotals";

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

const taxStore = useTaxStore();

const { calculateTotal } = useTotals({
    entry,
    taxStore,
});

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

const onItemAdded = (item) => {
    // Trigger calculation of totals when item is added via barcode
    calculateTotal();
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
    emitter.off("onStateChange");
    emitter.off("onTaxableChange");
});

onMounted(async () => {
    await taxStore.setupTaxes();
    if (props.mode === "edit") {
        const id = route.params.id;
        formModalTitle.value = modals.value.form.editTitle;
        await moduleFormStore.loadEditData(id);
        taxStore.setTaxable(entry.value.is_taxable);
        calculateTotal();
    } else {
        formModalTitle.value = modals.value.form.createTitle;
        moduleFormStore.resetEntry();
        taxStore.setTaxable(entry.value.is_taxable);
    }
    emitter.on("onTaxableChange", (data) => {
        taxStore.setTaxable(data.value);
        calculateTotal();
    });
    emitter.on("calculate-total", calculateTotal);
    emitter.on("onSalesOrderChange", moduleFormStore.onSalesOrderChange);
    emitter.on("onProductChange", async (payload) => {
        await moduleFormStore.onProductChange(payload);
        calculateTotal();
    });
    emitter.on("onStateChange", calculateTotal);
});

const resetBuyerCommissionDetails = () => {
    entry.value.agent = null;
    entry.value.agent_name = null;
    entry.value.commission = 0;
    moduleFormStore.onCommissionChange({ value: 0 });
};

const applyBuyerCommissionDetails = (buyerData) => {
    const commissionRate = Number(buyerData.commission_rate || 0);
    if (entry.value?.buyer && typeof entry.value.buyer === "object") {
        entry.value.buyer.agent = buyerData.agent ?? null;
        entry.value.buyer.agent_name = buyerData.agent_name ?? null;
        entry.value.buyer.commission_rate = buyerData.commission_rate ?? null;
    }
    entry.value.agent = buyerData.agent ?? null;
    if (buyerData.agent) {
        entry.value.agent_name =
            buyerData.agent.display_name ??
            buyerData.agent.name ??
            buyerData.agent_name ??
            null;
    } else {
        entry.value.agent_name = buyerData.agent_name ?? null;
    }
    entry.value.commission = commissionRate;
    moduleFormStore.onCommissionChange({ value: commissionRate });
};

watch(
    () => entry.value?.buyer?.id,
    async (buyerId) => {
        if (!buyerId) {
            resetBuyerCommissionDetails();
            return;
        }

        try {
            const response = await ApiService.get("buyers", {}, buyerId);
            const buyerData = response?.data?.data ?? {};
            applyBuyerCommissionDetails(buyerData);
        } catch (error) {
            $catchResponse(error);
            resetBuyerCommissionDetails();
        }
    },
    { immediate: true }
);


watch(
    () => entry.value?.agent?.id,
    (agentId) => {
        if (agentId) {
            const agentData = entry.value.agent ?? {};
            entry.value.agent_name =
                agentData.display_name ?? agentData.name ?? null;
            return;
        }

        if (entry.value?.buyer?.agent) {
            const buyerAgent = entry.value.buyer.agent;
            entry.value.agent_name =
                buyerAgent.display_name ??
                buyerAgent.name ??
                entry.value.buyer.agent_name ??
                null;
        } else if (entry.value?.buyer?.agent_name) {
            entry.value.agent_name = entry.value.buyer.agent_name;
        } else if (!entry.value?.buyer?.id) {
            entry.value.agent_name = null;
        }
    },
    { immediate: true }
);

</script>
