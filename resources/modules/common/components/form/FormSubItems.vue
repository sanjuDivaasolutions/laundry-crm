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
  -  *  Last modified: 13/01/25, 6:45 pm
  -  *  Written by Chintan Bagdawala, 2025.
  -  */
  -->

<template>
    <div class="text-center">
        <a href="#" @click.prevent="initModal(null)">{{ $t(label) }}</a>
        <FormModalList
            :id="modalId"
            :title="$t(label)"
            :fields="fields"
            :list-fields="listFields"
            size="modal-xl"
            @update:modelValue="onModalUpdate"
        />
    </div>
</template>

<script setup>
import { computed, ref } from "vue";
import emitter from "@/core/plugins/mitt";
import FormModalList from "@common@/modals/FormModalList.vue";

const props = defineProps({
    label: {
        type: String,
        default: "Items",
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
    entry: {
        type: Object,
        required: true,
    },
    parent: {
        type: Object,
        required: true,
    },
    mainEntry: {
        type: Object,
        required: true,
    },
    updateEvent: {
        type: String,
        default: null,
    },
});

const modalId = ref(`sub-items-${props.label}`);

const fields = computed(() => {
    return _.get(props.field, "subFields", []);
});

const listFields = computed(() => {
    return _.get(props.field, "listFields", []);
});

const parentField = computed(() => {
    return props.field.parentField;
});

const items = computed(() => {
    return props.modelValue || [];
});

const initModal = (id) => {
    let e = null;
    if (!id) {
        e = _.cloneDeep(props.field.entry);
        e[parentField.value] = props.parent.id;
    } else {
        e = _.cloneDeep(items.value.find((i) => i.id === id));
    }
    emitter.emit("show-form-modal-list", {
        id: modalId.value,
        entries: props.entry,
        entry: e,
        parent: props.mainEntry,
    });
};

const onModalUpdate = (data) => {
    props.modelValue = data;
    console.log(props.updateEvent);
    if (props.updateEvent) {
        emitter.emit(props.updateEvent, data);
    }
};

const removeItem = (item) => {
    const index = items.value.indexOf(item);
    items.value.splice(index, 1);
};
</script>

<style scoped></style>
