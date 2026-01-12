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
  -  *  Last modified: 22/01/25, 5:42 pm
  -  *  Written by Chintan Bagdawala, 2025.
  -  */
  -->

<template>
    <div class="row">
        <div
            v-if="textFields.length > 0"
            class="col-12"
            :class="{ 'mb-10': !noMargin }"
        >
            <div class="table-responsive">
                <table class="table align-middle table-bordered">
                    <tr v-for="f in textFields" :key="f.field">
                        <th v-if="hasLabel(f)" class="fw-bold">
                            {{ $t(f.label) }}
                        </th>
                        <td>{{ $getDisplayValue(entry, f.field) }}</td>
                    </tr>
                </table>
            </div>
        </div>
        <div
            v-if="itemFields.length > 0"
            v-for="f in itemFields"
            class="col-12"
            :class="{ 'mb-10': !noMargin }"
        >
            <h3 v-if="hasLabel(f)">{{ $t(f.label) }}</h3>
            <div class="table-responsive">
                <table class="table align-middle table-bordered">
                    <thead>
                        <tr>
                            <th
                                class="fw-bold bg-secondary-subtle"
                                :class="getFieldClass(f)"
                                v-for="f in f.subFields"
                            >
                                {{ $t(f.label) }}
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="itemFieldsLength(f) === 0">
                            <td
                                class="text-center"
                                :colspan="f.subFields.length"
                            >
                                No Item
                            </td>
                        </tr>
                        <tr
                            v-if="itemFieldsLength(f) > 0"
                            v-for="i in entry[f.field]"
                        >
                            <td
                                v-for="sf in f.subFields"
                                :class="getFieldClass(sf)"
                            >
                                <span v-if="hasLink(sf)">
                                    <RouterLink
                                        :to="getLink(i, sf)"
                                        target="_blank"
                                    >
                                        {{ $getDisplayValue(i, sf.field) }}
                                    </RouterLink>
                                </span>
                                <span class="fw-bold" v-else>{{
                                    $getDisplayValue(i, sf.field)
                                }}</span>
                                <span v-if="hasSubFields(sf, i)">
                                    <ShowValues
                                        :entry="i"
                                        :fields="sf.subFields"
                                        :no-margin="true"
                                    />
                                </span>
                            </td>
                        </tr>
                    </tbody>
                    <tfoot v-if="itemFieldsLength(f) > 0">
                        <tr>
                            <th
                                class="fw-bold"
                                :class="getFieldClass(f)"
                                v-if="f.field"
                                v-for="f in f.summaryFields"
                            >
                                {{ $getDisplayValue(entry, f.field, "") }}
                            </th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</template>

<script setup>
import { $getDisplayValue } from "@/core/helpers/utility";
import { computed } from "vue";

const props = defineProps({
    entry: {
        type: Object,
        required: true,
    },
    fields: {
        type: Array,
        required: true,
    },
    noMargin: {
        type: Boolean,
        default: false,
    },
});

const hasLink = (field) => {
    return _.get(field, "isLink", false);
};

const getLink = (item, field) => {
    const arr = field.field.split(".");
    if (!arr) return null;
    const obj = arr[0];
    if (!obj) return null;
    const linkField = `${obj}.link`;
    return _.get(item, linkField, null);
};

const textFields = computed(() => {
    return props.fields.filter(
        (f) => f.type === "text" || typeof f.type === "undefined"
    );
});

const itemFields = computed(() => {
    return props.fields.filter((f) => f.type === "items");
});

const itemFieldsLength = (f) => {
    return props.entry[f.field] ? props.entry[f.field].length : 0;
};

const getFieldClass = (f) => {
    let c = "";
    switch (f.align) {
        case "left":
            c = "text-start";
            break;
        case "center":
            c = "text-center";
            break;
        case "right":
            c = "text-end";
            break;
        default:
            c = "text-start";
    }
    return c;
};

const hasLabel = (f) => {
    return _.get(f, "label", false);
};

const hasSubFields = (f, entry) => {
    const subFields = _.get(f, "subFields", []);
    if (subFields.length === 0) return false;
    let result = false;
    subFields.forEach((sf) => {
        const v = _.get(entry, sf.field, []);
        if (v.length > 0) {
            result = true;
        }
    });
    console.log(result);
    return result;
};
</script>

<style scoped></style>
