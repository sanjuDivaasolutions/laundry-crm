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
  -  *  Last modified: 09/01/25, 6:09 pm
  -  *  Written by Chintan Bagdawala, 2025.
  -  */
  -->

<template>
    <div class="row">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h3 class="mb-0">{{ label }}</h3>
            <button
                v-if="allowNewItem"
                type="button"
                @click="addBlankItem"
                class="btn btn-primary btn-sm"
            >
                <FormIcon icon="feather:plus" />Add
            </button>
        </div>
        <div class="col-12">
            <table
                class="table align-middle table-row-bordered table-row-solid mb-0 gy-2 table-hover"
            >
                <thead>
                    <tr>
                        <th v-for="f in fields">{{ $t(f.label) }}</th>
                        <th v-if="allowRemoveItem" class="text-center">
                            Action
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="items.length < 1">
                        <td :colspan="fields.length + 1" class="text-center">
                            No Item
                        </td>
                    </tr>
                    <tr v-for="(i, idx) in items" v-if="items.length > 0">
                        <td
                            v-for="f in fields"
                            :style="`width:${f.width || 'auto'}`"
                        >
                            <component
                                :id="`${f.field}-${idx}`"
                                :field="f"
                                :class="f.field"
                                :is="getComponent(f)"
                                :name="f.name"
                                :label="getLabel(f)"
                                :type="f.type"
                                :min="f.min || null"
                                :max="f.max || null"
                                :placeholder="$t(f.placeholder || f.label)"
                                :required="getRequired(f)"
                                :endpoint="f.endpoint"
                                :id-value="f.idValue"
                                :label-value="f.labelValue"
                                :hide-selected="f.hideSelected"
                                :endpoint-filter-params="f.endpointFilterParams"
                                :dependent-field="f.dependentField"
                                :dependent-field-level="
                                    f.dependentFieldLevel ?? 'same'
                                "
                                :entry="i[f.field]"
                                :main-entry="i || null"
                                :parent="parent"
                                :mode="f.mode"
                                :model-value="i[f.field]"
                                v-model="i[f.field]"
                                :update-event="getUpdateEvent(f)"
                                :readonly="f.restrictEdit && mode === 'edit'"
                                :step="f.step || null"
                                :container-full-width="true"
                                :disabled="f.disabled"
                            />
                        </td>
                        <td
                            v-if="allowRemoveItem"
                            class="text-center"
                            style="width: auto"
                        >
                            <a
                                class="text-danger"
                                href="#"
                                @click.prevent="removeItem(i)"
                                title="Remove this item"
                                ><FormIcon icon="feather:trash"
                            /></a>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script setup>
import { computed } from "vue";
import FormIcon from "@common@/components/form/FormIcon.vue";
import emitter from "@/core/plugins/mitt";

const props = defineProps({
    label: {
        type: String,
        default: "Items",
    },
    modelValue: {
        type: Array,
        required: true,
    },
    parent: {
        type: Object,
        default: null,
    },
    mode: {
        type: String,
        default: null,
    },
    field: {
        type: Object,
        required: true,
    },
    allowNewItem: {
        type: Boolean,
        default: true,
    },
    allowRemoveItem: {
        type: Boolean,
        default: true,
    },
});

const blankObject = _.cloneDeep(props.field.entry);

const fields = computed(() => {
    return props.field.subFields;
});

const items = computed(() => {
    return props.modelValue || [];
});

const addBlankItem = () => {
    const obj = _.cloneDeep(blankObject);
    items.value.push(obj);
    const event = `${props.field}-items-update`;
    emitter.emit(event, items.value);
};

const getLabel = (field) => {
    switch (field.type) {
        case "sub-items":
            return field.label;
        default:
            return null;
    }
};

const getComponent = (field) => {
    switch (field.type) {
        case "text":
            return "FormInput";
        case "textarea":
            return "FormTextarea";
        case "amount":
            return "FormAmountLabel";
        case "date":
            return "FormDatepicker";
        case "select-ajax":
            return "FormSelectAjax";
        case "select-single":
            return "FormSelectSingle";
        case "select-currency":
            return "FormSelectCurrency";
        case "checkbox":
            return "FormCheckbox";
        case "sub-items":
            return "FormSubItems";
        default:
            return "FormInput";
    }
};

const getRequired = (field) => {
    switch (props.mode) {
        case "create":
            return field.required;
        case "edit":
            return field.required && !field.optionalOnEdit;
        default:
            return false;
    }
};

const getUpdateEvent = (field) => {
    return field.updateEvent || `${field.field}-update`;
};

const removeItem = (item) => {
    const c = confirm("Are you sure to remove this item?");
    if (!c) {
        return;
    }
    const index = items.value.indexOf(item);
    items.value.splice(index, 1);
};
</script>

<style scoped></style>
