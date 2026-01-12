<!--
  - /*
  -  *  Copyright (c) 2024 Divaa Solutions. All rights reserved.
  -  *
  -  *  This software is the confidential and proprietary information of Divaa Solutions
  -  *  ("Confidential Information"). You shall not disclose such Confidential Information and
  -  *  shall use it only in accordance with the terms of the license agreement you entered into
  -  *  with Divaa Solutions.
  -  *
  -  *  Unauthorized copying of this file, via any medium is strictly prohibited.
  -  *  Proprietary and confidential.
  -  *
  -  *  Last modified: 17/10/24, 5:53 pm
  -  *  Written by Chintan Bagdawala, 2024.
  -  */
  -->

<template>
    <div class="row">
        <div class="col-12 d-flex justify-content-between align-items-center flex-wrap gap-3">
            <h3 class="mb-0">{{ label }}</h3>
            <div
                v-if="headerFields.length"
                class="d-flex align-items-center flex-wrap gap-4"
            >
                <component
                    v-for="hf in headerFields"
                    :key="hf.name"
                    :field="hf"
                    :class="hf.field"
                    :is="getComponent(hf)"
                    :name="hf.name"
                    :type="hf.type"
                    :min="hf.min || null"
                    :max="hf.max || null"
                    :step="hf.step || null"
                    :label="$t(hf.label)"
                    :placeholder="$t(hf.placeholder || hf.label)"
                    :required="getRequired(hf)"
                    :endpoint="hf.endpoint"
                    :endpoint-filter-params="hf.endpointFilterParams"
                    :dependent-field="hf.dependentField"
                    :id-value="hf.idValue"
                    :label-value="hf.labelValue"
                    :hide-selected="hf.hideSelected"
                    :entry="getHeaderEntry(hf)"
                    :main-entry="mainEntry"
                    :parent="getHeaderEntry(hf)"
                    :mode="mode"
                    :model-value="getHeaderModelValue(hf)"
                    @update:model-value="setHeaderValue(hf, $event)"
                    :currency="getCurrency(hf)"
                    :currency-rate-field="hf.currencyRateField || 'currency_rate'"
                    :update-event="getUpdateEvent(hf)"
                    :object="typeof hf.object === 'boolean' ? hf.object : true"
                    :readonly="hf.restrictEdit && mode === 'edit'"
                    :container-full-width="false"
                    :disabled="getDisabled(hf)"
                />
            </div>
        </div>
        <div class="col-12" v-if="!isGroupHidden">
            <div class="row d-flex">
                <div
                    class="mb-5"
                    :class="f.column ? `col-${f.column}` : 'col-12'"
                    v-for="f in fields"
                    :key="f.name"
                >
                    <component
                        v-show="isVisible(f)"
                        :field="f"
                        :class="f.field"
                        :is="getComponent(f)"
                        :name="f.name"
                        :type="f.type"
                        :min="f.min || null"
                        :max="f.max || null"
                        :label="$t(f.label)"
                        :placeholder="$t(f.placeholder || f.label)"
                        :required="getRequired(f)"
                        :endpoint="f.endpoint"
                        :endpoint-filter-params="f.endpointFilterParams"
                        :dependent-field="f.dependentField"
                        :id-value="f.idValue"
                        :label-value="f.labelValue"
                        :hide-selected="f.hideSelected"
                        :entry="f.field !== 'entry' ? entry[f.field] : entry"
                        :main-entry="entry"
                        :parent="entry || null"
                        :mode="f.mode"
                        :model-value="
                            f.field !== 'entry' ? entry[f.field] : entry
                        "
                        @update:model-value="
                            f.field !== 'entry'
                                ? (entry[f.field] = $event)
                                : (entry = $event)
                        "
                        :currency="getCurrency(f)"
                        :currency-rate-field="
                            f.currencyRateField || 'currency_rate'
                        "
                        :update-event="getUpdateEvent(f)"
                        :object="
                            typeof f.object === 'boolean' ? f.object : true
                        "
                        :readonly="f.restrictEdit && mode === 'edit'"
                        :container-full-width="true"
                        :disabled="getDisabled(f)"
                    />
                </div>
            </div>
        </div>
        <div class="col-12" v-else>
            <div class="alert alert-info mb-5" role="alert">
                {{ $t(field.hideMessage || 'Shipping address matches billing address.') }}
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from "vue";

const props = defineProps({
    label: {
        type: String,
        default: "Group",
    },
    modelValue: {
        type: Array,
        required: true,
    },
    mode: {
        type: String,
        default: null,
    },
    field: {
        type: Object,
        required: true,
    },
    mainEntry: {
        type: Object,
        required: true,
    },
    entry: {
        type: Object,
        required: true,
    },
});

const fields = computed(() => {
    return props.field.subFields;
});

const headerFields = computed(() => {
    return props.field.headerFields || [];
});

const getModelValue = (field) => {
    if (props.entry) {
        return props.entry[field.field];
    }
    return null;
};

const getCurrency = (field) => {
    if (field.type === "select-currency") {
        return props.entry[field.currencyField];
    }
    return null;
};

const getUpdateEvent = (field) => {
    return field.updateEvent || `${field.field}-update`;
};

const getComponent = (field) => {
    switch (field.type) {
        case "text":
            return "FormInput";
        case "textarea":
            return "FormTextarea";
        case "switch":
            return "FormSwitch";
        case "date":
            return "FormDatepicker";
        case "select-ajax":
            return "FormSelectAjax";
        case "checkbox-inline":
            return "FormCheckboxInline";
        case "select-single":
            return "FormSelectSingle";
        case "select-currency":
            return "FormSelectCurrency";
        case "select-multiple":
            return "FormSelectMultiple";
        case "items":
            return "FormItems";
        case "invoice-items":
            return "FormInvoiceItems";
        default:
            return "FormInput";
    }
};

const getRequired = (field) => {
    switch (props.mode) {
        case "edit":
            return field.required && !field.optionalOnEdit;
        default:
            return field.required;
    }
};

const evaluateCondition = (condition) => {
    if (!condition) {
        return false;
    }
    if (typeof condition === "string") {
        return Boolean(_.get(props.mainEntry, condition));
    }
    const target = condition.target === "group" ? props.entry : props.mainEntry;
    const value = _.get(target, condition.field);
    if (Object.prototype.hasOwnProperty.call(condition, "value")) {
        return _.isEqual(value, condition.value);
    }
    return Boolean(value);
};

const getDisabled = (field) => {
    if (typeof field.disabled === "boolean") {
        return field.disabled;
    }
    return evaluateCondition(field.disabledIf);
};

const isGroupHidden = computed(() => {
    return evaluateCondition(props.field.hideWhen);
});


const isVisible = (field) => {
    if (field && typeof field.visibleIf === "object") {
        const entryValue = field.visibleIf.field
            ? props.mainEntry[field.visibleIf.field]
            : props.mainEntry;
        if (entryValue && field.visibleIf.key) {
            return _.isEqual(
                _.get(entryValue, [field.visibleIf.key]),
                field.visibleIf.value
            );
        }
        return _.isEqual(
            props.entry[field.visibleIf.field],
            field.visibleIf.value
        );
    }
    return true;
};

const getHeaderEntry = (field) => {
    if (field.target === "group") {
        return props.entry;
    }
    return props.mainEntry;
};

const getHeaderModelValue = (field) => {
    const target = getHeaderEntry(field);
    return _.get(target, field.field);
};

const setHeaderValue = (field, value) => {
    const target = getHeaderEntry(field);
    if (!target) return;
    if (field.field && field.field.indexOf(".") !== -1) {
        _.set(target, field.field, value);
    } else {
        target[field.field] = value;
    }
    if (typeof field.onChange === "function") {
        field.onChange(value, target, props.mainEntry);
    }
};
</script>

<style scoped></style>
