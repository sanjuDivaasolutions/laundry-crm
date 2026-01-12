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
import { useModuleFormStore } from "@modules@/purchaseInvoices/purchaseInvoicesFormStore";
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
                    $toastSuccess("Purchase invoice created successfully");
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
                    $toastSuccess("Purchase invoice updated successfully");
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
        let taxRate = 0;
        entry.value.items.forEach((item) => {
            if (!item.quantity || !item.rate) return;
            const total = item.quantity * item.rate;
            item.amount = total;
            subTotal += total;
        });
        const taxTotal = subTotal * (taxRate / 100);
        const grandTotal = subTotal + taxTotal;

        entry.value.tax_total = taxTotal.toFixed(2);
        entry.value.sub_total = subTotal.toFixed(2);
        entry.value.grand_total = grandTotal.toFixed(2);
    };

const redirectIndex = () => {
    router.push({
        name: `${module.value.id}.index`,
    });
};
const onUpdateProducts  = (payload) => {
    var productId = payload.value.id;
    var item = entry.value.items.find((i) => i.product.id === productId)
    if(item){
        item.unit = payload.value.unit;
        item.rate =  payload.value.rate;
        calculateTotal();
    }
    
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
    emitter.off("onPurchaseOrderChange");
      emitter.off("onUpdateProducts");
    //emitter.off("currency-update");
   // emitter.off("currency_rate-update");
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
     emitter.on("onUpdateProducts", onUpdateProducts);
     emitter.on("onPurchaseOrderChange", moduleFormStore.onPurchaseOrderChange);
    //emitter.on("currency-update", moduleFormStore.onCurrencyUpdate);
    //emitter.on("currency_rate-update", moduleFormStore.onCurrencyRateUpdate);
});
</script>
