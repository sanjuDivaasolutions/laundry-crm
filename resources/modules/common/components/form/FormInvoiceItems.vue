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
  -  *  Last modified: 22/01/25, 4:38 pm
  -  *  Written by Chintan Bagdawala, 2025.
  -  */
  -->

<template>
    <div class="row mt-3">
        <div
            class="col-12 d-flex justify-content-between align-items-center mb-3"
        >
            <h3>Items</h3>
            <button
                type="button"
                @click="addBlankItem"
                class="btn btn-primary btn-sm d-flex align-items-center"
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
                        <th v-for="f in fields" :class="f.columnClass || ''">
                            {{ $t(f.label) }}
                        </th>
                        <th class="text-end">Action</th>
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
                            v-for="(f, fIdx) in fields"
                            :class="f.columnClass || ''"
                        >
                            <component
                                :key="fIdx"
                                :index="idx"
                                :field="f"
                                :class="f.class"
                                :is="getComponent(f)"
                                :name="f.name"
                                :type="f.type"
                                :placeholder="$t(f.placeholder || f.label)"
                                :required="getRequired(f)"
                                :endpoint="f.endpoint"
                                :endpoint-filter-params="f.endpointFilterParams"
                                :dependent-field="f.dependentField"
                                :id-value="f.idValue"
                                :label-value="f.labelValue"
                                :hide-selected="f.hideSelected"
                                :entry="i[f.field]"
                                :main-entry="i || null"
                                :parent="parent"
                                :mode="f.mode"
                                :model-value="i[f.field]"
                                :step="f.step || null"
                                @update:modelValue="
                                    updateValue(i, f.field, $event)
                                "
                                :readonly="
                                    (f.restrictEdit && mode === 'edit') ||
                                    f.readonly
                                "
                                :container-full-width="true"
                                :update-event="getUpdateEvent(f)"
                            />
                        </td>
                        <td class="text-end">
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
                <tfoot v-if="items.length > 0">
                    <tr>
                        <th class="text-end" :colspan="fields.length - 1">
                            <strong>{{ $t("general.fields.subTotal") }}</strong>
                        </th>
                        <th class="text-end">
                            <FormAmountLabel
                                :model-value="getTotal(field, 'subTotal')"
                            />
                        </th>
                        <th>&nbsp;</th>
                    </tr>
                    <tr v-if="!hasMultipleTaxField">
                        <th class="text-end" :colspan="fields.length - 1">
                            <strong>TAX (5%)</strong>
                        </th>
                        <th class="text-end">
                            <FormAmountLabel
                                :model-value="getTotal(field, 'taxTotal')"
                            />
                        </th>
                        <th>&nbsp;</th>
                    </tr>
                    <tr v-if="hasMultipleTaxField" v-for="tax in taxes">
                        <th class="text-end" :colspan="fields.length - 1">
                            <strong>{{ tax.name }}</strong>
                        </th>
                        <th class="text-end">
                            <FormAmountLabel :model-value="tax.amount" />
                        </th>
                        <th>&nbsp;</th>
                    </tr>
                    <tr>
                        <th class="text-end" :colspan="fields.length - 1">
                            <strong>{{
                                $t("general.fields.grandTotal")
                            }}</strong>
                        </th>
                        <th class="text-end">
                            <FormAmountLabel
                                :model-value="getTotal(field, 'grandTotal')"
                            />
                        </th>
                        <th>&nbsp;</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted } from "vue";
import FormIcon from "@common@/components/form/FormIcon.vue";
import emitter from "@/core/plugins/mitt";
import FormAmountLabel from "@common@/components/form/FormAmountLabel.vue";
import Swal from "sweetalert2";

const props = defineProps({
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
    parent: {
        type: Object,
        default: null,
    },
});

const blankObject = _.cloneDeep(props.field.entry);

const fields = computed(() => {
    return props.field.subFields;
});
const getUpdateEvent = (field) => {
    return field.updateEvent || `${field.field}-update`;
};

const items = computed(() => {
    return props.modelValue || [];
});

const taxes = computed(() => {
    const taxField = _.get(props.field, "totalFields.multipleTaxField", null);
    if (!taxField) return [];
    return _.get(props.mainEntry, taxField, null);
});

const hasMultipleTaxField = computed(() => {
    return _.get(props.field, "totalFields.multipleTaxField", null) !== null;
});

const getTotal = (field, type) => {
    let totalField = null;
    if (type === "subTotal") {
        totalField =
            _.get(field, "totalFields.subTotalLabel") ||
            _.get(field, "totalFields.subTotal");
    }
    if (type === "taxTotal") {
        totalField =
            _.get(field, "totalFields.taxTotalLabel") ||
            _.get(field, "totalFields.taxTotal");
    }
    if (type === "grandTotal") {
        totalField =
            _.get(field, "totalFields.grandTotalLabel") ||
            _.get(field, "totalFields.grandTotal");
    }
    return totalField ? _.get(props.mainEntry, totalField) : 0;
};

const addBlankItem = () => {
    const obj = _.cloneDeep(blankObject);
    items.value.push(obj);
    const event = `invoice-items-update`;
    emitter.emit(event, items.value);
};

const updateValue = (item, field, value) => {
    item[field] = value;
    emitter.emit("calculate-total");
};

const getComponent = (field) => {
    switch (field.type) {
        case "text":
            return "FormInput";
        case "amount":
            return "FormAmountLabel";
        case "select-ajax":
            return "FormSelectAjax";
        case "select-single":
            return "FormSelectSingle";
        case "checkbox":
            return "FormCheckbox";
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

const removeItem = (item) => {
    Swal.fire({
        title: "Are you sure?",
        text: "You won't be able to revert this!",
        type: "warning",
        showCancelButton: true,
        confirmButtonText: "Delete",
        confirmButtonColor: "#dd4b39",
        focusCancel: true,
        reverseButtons: true,
    }).then((r) => {
        if (!r.isConfirmed) return;
        const index = items.value.indexOf(item);
        items.value.splice(index, 1);
    });
};

onMounted(() => {
    fields.value.forEach((f) => {
        if (f.source) {
            emitter.on(`update:${f.source.control}`, (value) => {});
        }
    });
});

onBeforeUnmount(() => {
    fields.value.forEach((f) => {
        if (f.source) {
            emitter.off(`update:${f.source.control}`);
        }
    });
});
</script>

<style scoped></style>
