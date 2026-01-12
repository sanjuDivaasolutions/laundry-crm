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
  -  *  Last modified: 13/01/25, 5:35 pm
  -  *  Written by Chintan Bagdawala, 2025.
  -  */
  -->

<template>
    <form
        @submit.prevent="handleSubmit"
        autocomplete="off"
        v-if="optionsPreloaded"
    >
        <div class="row d-flex">
            <div
                :class="formItemClass(f)"
                v-for="f in fields"
                :key="f.name"
                v-show="isVisible(f)"
            >
                <component
                    :field="f"
                    :class="f.field"
                    :is="getComponent(f)"
                    :name="f.name"
                    :type="f.type"
                    :min="f.min || null"
                    :max="f.max || null"
                    :step="f.step || null"
                    :label="
                        fieldLabel || f.type === 'checkbox-inline'
                            ? $t(f.label)
                            : null
                    "
                    :placeholder="$t(f.placeholder || f.label)"
                    :required="getRequired(f)"
                    :endpoint="f.endpoint"
                    :endpoint-filter-params="f.endpointFilterParams"
                    :dependent-field="f.dependentField"
                    :id-value="f.idValue"
                    :label-value="f.labelValue"
                    :hide-selected="f.hideSelected"
                    :entry="f.field !== 'entry' ? entry[f.field] : entry"
                    :parent="parent || entry || null"
                    :main-entry="entry"
                    :mode="f.mode"
                    :config="f.config || {}"
                    :model-value="f.field !== 'entry' ? entry[f.field] : entry"
                    @update:model-value="
                        f.field !== 'entry'
                            ? (entry[f.field] = $event)
                            : (entry = $event)
                    "
                    :currency="getCurrency(f)"
                    :disabled="f.disabled"
                    :currency-rate-field="
                        f.currencyRateField || 'currency_rate'
                    "
                    :update-event="getUpdateEvent(f)"
                    :object="typeof f.object === 'boolean' ? f.object : true"
                    :readonly="f.restrictEdit && mode === 'edit'"
                    :container-full-width="true"
                    :allow-new-item="
                        typeof f.allowNewItem === 'boolean'
                            ? f.allowNewItem
                            : true
                    "
                    :allow-remove-item="
                        typeof f.allowRemoveItem === 'boolean'
                            ? f.allowNewItem
                            : true
                    "
                />
            </div>
            <div
                class="d-flex justify-content-center"
                :class="inline ? 'col-auto' : 'col-12 mt-3'"
            >
                <div
                    :class="
                        inline ? 'input-group' : 'd-flex justify-content-center'
                    "
                >
                    <button
                        type="submit"
                        class="d-flex align-items-center"
                        :class="submitClass"
                        :disabled="isLoading"
                    >
                        <FormIcon
                            v-if="!isLoading"
                            :icon="submitIcon"
                            class="mr-2"
                        />
                        <span v-if="!isLoading">{{ $t(submitText) }}</span>
                        <span v-if="isLoading">Please wait...</span>
                    </button>
                    <button
                        v-if="showCancel"
                        type="button"
                        class="d-flex align-items-center"
                        :class="cancelClass"
                        @click="handleCancel"
                        :disabled="isLoading"
                    >
                        <FormIcon :icon="cancelIcon" class="mr-2" />
                        {{ $t(cancelText) }}
                    </button>
                </div>
            </div>
        </div>
    </form>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted } from "vue";
import ApiService from "@/core/services/ApiService";
import { useOptionStore } from "@common@/components/optionStore";
import emitter from "@/core/plugins/mitt";

const props = defineProps({
    fields: {
        type: Array,
        required: true,
    },
    mode: {
        type: String,
        default: "create",
    },
    loading: {
        type: Boolean,
        default: false,
    },
    inline: {
        type: Boolean,
        default: false,
    },
    fieldLabel: {
        type: Boolean,
        default: true,
    },
    defaultsRoute: {
        type: String,
        default: null,
    },
    defaultsRouteAfterEvent: {
        type: String,
        default: null,
    },
    entry: {
        type: Object,
        required: true,
    },
    parent: {
        type: Object,
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
    submitClass: {
        type: String,
        default: "btn btn-primary",
    },
    showCancel: {
        type: Boolean,
        default: true,
    },
    cancelText: {
        type: String,
        default: "general.fields.cancel",
    },
    cancelIcon: {
        type: String,
        default: "feather:x",
    },
    cancelClass: {
        type: String,
        default: "btn btn-secondary ms-4",
    },
});

const emit = defineEmits(["submit", "cancel"]);

const getModal = (f) => {
    return f.field !== "entry" ? entry[f.field] : entry;
};

const isLoading = computed(() => {
    return props.loading;
});

const formItemClass = (f) => {
    if (props.inline) {
        return "col-auto";
    }
    return f.column ? `col-md-${f.column} mb-5` : "col-12 mb-5";
};

const getComponent = (field) => {
    switch (field.type) {
        case "group-button":
            return "FormGroupButton";
        case "text":
            return "FormInput";
        case "decimal":
            return "FormDecimal";
        case "textarea":
            return "FormTextarea";
        case "ckeditor":
            return "FormCkEditor";
        case "switch":
            return "FormSwitch";
        case "date":
            return "FormDatepicker";
        case "checkbox-inline":
            return "FormCheckboxInline";
        case "checkbox":
            return "FormCheckbox";
        case "checkbox-group":
            return "FormCheckboxGroup";
        case "date-range":
            return "FormDateRangePicker";
        case "select-ajax":
            return "FormSelectAjax";
        case "select-single":
            return "FormSelectSingle";
        case "select-currency":
            return "FormSelectCurrency";
        case "select-multiple":
            return "FormSelectMultiple";
        case "items":
            return "FormItems";
        case "sum-items":
            return "FormSumItems";
        case "invoice-items":
            return "FormInvoiceItems";
        case "group":
            return "FormGroup";
        case "file-single":
            return "FormFileSingle";
        case "file-drop":
            return "FormFileDrop";
        case "barcode":
            return "BarcodeField";
        default:
            return "FormInput";
    }
};

const getRequired = (field) => {
    let isRequired = false;
    switch (props.mode) {
        case "create":
            isRequired = field.required;
            break;
        case "edit":
            isRequired = field.required && !field.optionalOnEdit;
            break;
    }
    if (isRequired) {
        isRequired = isVisible(field);
    }
    return isRequired;
};

const getUpdateEvent = (field) => {
    return field.updateEvent || `${field.field}-update`;
};

const getCurrency = (field) => {
    if (field.type === "select-currency") {
        return props.entry[field.currencyField];
    }
    return null;
};

const isVisible = (field) => {
    if (field && typeof field.visibleIf === "object") {
        const entryValue = props.entry[field.visibleIf.field];
        if (entryValue && field.visibleIf.key) {
            const value = field.visibleIf.value.split(",");
            const compareValue = _.get(entryValue, [field.visibleIf.key]);
            return value.indexOf(compareValue) > -1;
        }
        return props.entry[field.visibleIf.field] === field.visibleIf.value;
    }
    const showInMode = field.showInMode || ["create", "edit"];
    return showInMode.indexOf(props.mode) > -1;
};

const handleSubmit = () => {
    emit("submit", props.entry);
};
const handleCancel = () => {
    emit("cancel", props.entry);
};

const onCurrencyUpdate = (currency) => {
    const currencyRateField = _.get(
        currency,
        "currencyRateField",
        "currency_rate"
    );
    props.entry[currencyRateField] = _.get(currency, "obj.targetValue", 1);
};

const options = useOptionStore();

const optionsPreloaded = computed(() => {
    return options.preloaded;
});

onMounted(async () => {
    if (props.mode === "create" && props.defaultsRoute) {
        console.log("FormFields calling defaults route:", props.defaultsRoute);
        ApiService.get(props.defaultsRoute).then((response) => {
            console.log("FormFields defaults response:", response.data);  
            if (response.data && response.data.defaults) {
                Object.keys(response.data.defaults).forEach((key) => {
                    props.entry[key] = response.data.defaults[key];
                });
                if (props.defaultsRouteAfterEvent) {
                    emitter.emit(
                        props.defaultsRouteAfterEvent,
                        response.data.defaults
                    );
                }
            }
        });
    }
    emitter.on("currency-update", onCurrencyUpdate);
});

onBeforeUnmount(() => {
    options.clearOptions();
});
</script>

<style scoped></style>
