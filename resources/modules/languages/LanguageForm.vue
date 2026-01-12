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
                :loading="isLoading"
                :show-cancel="true"
                :defaults-route="defaultRoute"
            />
        </template>
    </card-container>
</template>

<script setup>
import FormFields from "@common@/components/FormFields.vue";
import { useModuleFormStore } from "@modules@/languages/languagesFormStore";
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

const isLoading = ref(false);

const onSubmit = (formEntry) => {
    switch (props.mode) {
        case "create":
            isLoading.value = true;
            ApiService.post(moduleFormStore.route, formEntry)
                .then((response) => {
                    $toastSuccess("Language was created successfully");
                    redirectIndex();
                })
                .catch((error) => {
                    $catchResponse(error);
                })
                .finally(() => {
                    isLoading.value = false;
                });
            break;
        case "edit":
            isLoading.value = true;
            ApiService.put(
                `${moduleFormStore.route}/${formEntry.id}`,
                formEntry
            )
                .then((response) => {
                    $toastSuccess("Language was updated successfully");
                    redirectIndex();
                })
                .catch((error) => {
                    $catchResponse(error);
                })
                .finally(() => {
                    isLoading.value = false;
                });
            break;
    }
};

const defaultRoute = computed(() => {
    return `${moduleFormStore.route}/create`;
});

const calculateTotal = () => {
    let subTotal = 0;
    entry.value.items.forEach((item) => {
        if (!item.quantity || !item.rate) return;
        const total = item.quantity * item.rate;
        item.amount = total;
        subTotal += total;
    });
    entry.value.sub_total = subTotal;
    entry.value.grand_total = subTotal;
};

const redirectIndex = () => {
    router.push({
        name: `${module.value.id}.index`,
    });
};

const onBuyerCurrencyUpdate = (e) => {
    moduleFormStore.onCurrencyUpdate(
        e,
        true,
        "buyer_currency",
        "buyer_currency_rate"
    );
};

const onBuyerCurrencyRateUpdate = (e) => {
    moduleFormStore.onCurrencyRateUpdate(
        e,
        "buyer_currency",
        "buyer_currency_rate"
    );
};

const onSupplierCurrencyUpdate = (e) => {
    moduleFormStore.onCurrencyUpdate(
        e,
        true,
        "supplier_currency",
        "supplier_currency_rate"
    );
};

const onSupplierCurrencyRateUpdate = (e) => {
    moduleFormStore.onCurrencyRateUpdate(
        e,
        "supplier_currency",
        "supplier_currency_rate"
    );
};

onBeforeUnmount(() => {
    moduleFormStore.resetEntry();
    emitter.off("calculate-total");
    emitter.off("buyer-currency-update");
    emitter.off("buyer_currency_rate-update");
    emitter.off("supplier-currency-update");
    emitter.off("supplier_currency_rate-update");
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
    emitter.on("buyer-currency-update", onBuyerCurrencyUpdate);
    emitter.on("buyer_currency_rate-update", onBuyerCurrencyRateUpdate);
    emitter.on("supplier-currency-update", onSupplierCurrencyUpdate);
    emitter.on("supplier_currency_rate-update", onSupplierCurrencyRateUpdate);
});
</script>
