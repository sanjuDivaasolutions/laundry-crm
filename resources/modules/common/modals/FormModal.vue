<template>
    <ModalContainer
        :id="id"
        :title="title"
        :size="size"
        :centered="centered"
        :backdrop="backdrop"
        :narrow-padding="narrowPadding"
        @close="onCancel"
    >
        <template #body>
            <slot name="modal-body">
                <div :class="formContainerClass">
                    <FormFields
                        v-if="modalVisible"
                        :fields="fields"
                        :entry="entry"
                        :mode="mode"
                        @submit="onSubmit"
                        @cancel="onCancel"
                        :loading="isLoading"
                        :defaults-route="defaultsRoute"
                        :submit-text="submitText"
                        :submit-icon="submitIcon"
                    />
                </div>
            </slot>
            <slot name="modal-after-body"></slot>
        </template>
    </ModalContainer>
</template>

<script setup>
import ModalContainer from "@common@/modals/ModalContainer.vue";
import FormFields from "@common@/components/FormFields.vue";
import emitter from "@/core/plugins/mitt";
import { computed, onBeforeUnmount, onMounted, ref } from "vue";
import ApiService from "@/core/services/ApiService";
import { $catchResponse, $toastSuccess } from "@/core/helpers/utility";
import { useOptionStore } from "@common@/components/optionStore";

const props = defineProps({
    id: {
        type: String,
        required: true,
    },
    title: {
        type: String,
        default: "",
    },
    fields: {
        type: Array,
        required: true,
        default: [],
    },
    mode: {
        type: String,
        default: null,
    },
    route: {
        type: String,
        required: true,
    },
    size: {
        type: String,
        default: "modal-lg",
    },
    narrowPadding: {
        type: Boolean,
        default: false,
    },
    border: {
        type: Boolean,
        default: false,
    },
    centered: {
        type: Boolean,
        default: true,
    },
    backdrop: {
        type: Boolean,
        default: true,
    },
    defaultsRoute: {
        type: String,
        default: null,
    },
    createSubmitEvent: {
        type: String,
        default: null,
    },
    updateSubmitEvent: {
        type: String,
        default: null,
    },
    optionStoreValue: {
        type: String,
        default: null,
    },
    submitText: {
        type: String,
        default: "general.fields.save",
    },
    submitIcon: {
        type: String,
        default: "feather:save",
    },
    local: {
        type: Boolean,
        default: false,
    },
});

const entry = ref({});
const modalVisible = ref(false);

const emit = defineEmits(["submit", "cancel"]);

const isLoading = ref(false);

const formContainerClass = computed(() => {
    return props.border ? "border border-light rounded py-7 px-10" : "";
});

const formFields = computed(() => {
    return modalVisible.value ? props.fields : [];
});

const optionStore = useOptionStore();

const onSubmit = () => {
    if (props.local) {
        onLocalSubmit();
    } else {
        onApiSubmit(entry.value);
    }
};

const onLocalSubmit = () => {
    const e = _.cloneDeep(entry.value);
    entry.value = {};
    emit("submit", e);
    closeModal();
};

const onApiSubmit = (formEntry) => {
    switch (props.mode) {
        case "create":
            isLoading.value = true;
            ApiService.post(props.route, formEntry)
                .then((response) => {
                    $toastSuccess("Successfully added!");
                    refreshTableData();
                    if (props.createSubmitEvent) {
                        emitter.emit(props.createSubmitEvent, response.data);
                    }
                    reloadOption();
                    closeModal();
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
            ApiService.put(`${props.route}/${formEntry.id}`, formEntry)
                .then((response) => {
                    $toastSuccess("Successfully updated!");
                    refreshTableData();
                    if (props.updateSubmitEvent) {
                        emitter.emit(props.updateSubmitEvent, response.data);
                    }
                    reloadOption();
                    closeModal();
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

const reloadOption = () => {
    if (!props.optionStoreValue) return;
    optionStore.reloadOption(props.optionStoreValue);
};

const onCancel = () => {
    emit("cancel");
    closeModal();
};

const closeModal = () => {
    emitter.emit("hide-modal-container", { id: props.id });
    setTimeout(() => (modalVisible.value = false), 300);
};

const refreshTableData = () => {
    emitter.emit("form-modal-refresh-magic-table-data");
};

onMounted(() => {
    emitter.on("show-form-modal", (payload) => {
        entry.value = payload.entry;
        emitter.emit("show-modal-container", payload);
        modalVisible.value = true;
    });
    emitter.on(`update-modal-entry-field-${props.id}`, (payload) => {
        entry.value[payload.field] = payload.value;
    });
});

onBeforeUnmount(() => {
    emitter.off("show-form-modal");
    emitter.off(`update-modal-entry-field-${props.id}`);
});
</script>

<style scoped></style>
