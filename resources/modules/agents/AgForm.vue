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
import { useModuleFormStore } from "@modules@/agents/agentsFormStore";
import { computed, onBeforeUnmount, onMounted, ref, watch } from "vue";
import ApiService from "@/core/services/ApiService";
import { $catchResponse, $toastSuccess } from "@/core/helpers/utility";
import router from "@/router";
import { useRoute } from "vue-router";
import { defaultEntry } from "./agentsFormData";

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
const formModalTitle = ref("Agent");

const getEmptyShippingAddress = () => _.cloneDeep(defaultEntry().shipping_address);

const shippingBackup = ref(null);

const copyBillingToShipping = () => {
    if (!entry.value) return;
    const billing = _.cloneDeep(entry.value.billing_address || getEmptyShippingAddress());
    if (entry.value.billing_address && entry.value.billing_address.id) {
        billing.id = entry.value.billing_address.id;
    }
    entry.value.shipping_address = billing;
};

watch(
    () => entry.value,
    (newVal) => {
        shippingBackup.value = null;
        if (!newVal) return;
        if (typeof newVal.shipping_same_as_billing !== "boolean") {
            newVal.shipping_same_as_billing = false;
        }
        if (newVal.shipping_same_as_billing) {
            copyBillingToShipping();
        }
    },
    { immediate: true }
);

watch(
    () => entry.value?.shipping_same_as_billing,
    (same) => {
        if (!entry.value) return;
        if (same) {
            shippingBackup.value = _.cloneDeep(entry.value.shipping_address || getEmptyShippingAddress());
            copyBillingToShipping();
        } else {
            const restored = shippingBackup.value
                ? _.cloneDeep(shippingBackup.value)
                : getEmptyShippingAddress();
            if (
                restored &&
                entry.value.billing_address &&
                restored.id &&
                entry.value.billing_address.id &&
                restored.id === entry.value.billing_address.id
            ) {
                restored.id = null;
            }
            entry.value.shipping_address = restored;
            shippingBackup.value = null;
        }
    }
);

watch(
    () => entry.value?.billing_address,
    () => {
        if (entry.value?.shipping_same_as_billing) {
            copyBillingToShipping();
        }
    },
    { deep: true }
);

const onSubmit = (formEntry) => {
    switch (props.mode) {
        case "create":
            ApiService.post(moduleFormStore.route, formEntry)
                .then((response) => {
                    $toastSuccess("Agent created successfully");
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
                    $toastSuccess("Agent updated successfully");
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

const ensureAgentFlag = () => {
    if (entry.value) {
        entry.value.is_agent = true;
    }
};

onBeforeUnmount(() => {
    moduleFormStore.resetEntry();
    shippingBackup.value = null;
});

onMounted(async () => {
    if (props.mode === "edit") {
        formModalTitle.value = modals.value.form.editTitle;
        const id = route.params.id;
        await moduleFormStore.loadEditData(id);
        ensureAgentFlag();
    } else {
        formModalTitle.value = modals.value.form.createTitle;
        moduleFormStore.resetEntry();
        ensureAgentFlag();
    }
});
</script>
