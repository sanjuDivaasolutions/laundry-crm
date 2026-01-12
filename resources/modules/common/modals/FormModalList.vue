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
  -  *  Last modified: 13/01/25, 6:11 pm
  -  *  Written by Chintan Bagdawala, 2025.
  -  */
  -->

<template>
    <ModalContainer :id="id" :title="title" :size="size" @close="closeModal">
        <template #body>
            <slot name="modal-body">
                <div class="d-flex justify-content-end align-items-center mb-4">
                    <a
                        href="#"
                        @click.prevent="initSubFormModal(null)"
                        class="btn btn-primary btn-sm d-flex align-items-center"
                        ><FormIcon icon="feather:plus" />New Item</a
                    >
                    <FormModalLocal
                        :id="formId"
                        :fields="fields"
                        :entry="entry"
                        :parent="parent"
                        @submit="onSubmit"
                    />
                </div>
                <table
                    class="table align-middle table-row-bordered table-row-solid mb-0 gy-2 gs-9 table-hover"
                >
                    <thead>
                        <tr>
                            <th v-for="f in listFields">{{ $t(f.label) }}</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="entries.length > 0" v-for="e in entries">
                            <td v-for="f in listFields">
                                {{ $getDisplayValue(e, f.field) }}
                            </td>
                            <td>
                                <a
                                    href="#"
                                    class="text-warning me-2"
                                    @click.prevent="initSubFormModal(e.id)"
                                    ><FormIcon icon="feather:edit-2"
                                /></a>
                                <a
                                    href="#"
                                    class="text-danger"
                                    @click.prevent="removeItem(e)"
                                    ><FormIcon icon="feather:trash"
                                /></a>
                            </td>
                        </tr>
                        <tr v-if="entries.length === 0">
                            <td
                                class="text-center"
                                :colspan="fields.length + 1"
                            >
                                No Item
                            </td>
                        </tr>
                    </tbody>
                </table>
            </slot>
        </template>
    </ModalContainer>
</template>

<script setup>
import ModalContainer from "@common@/modals/ModalContainer.vue";
import emitter from "@/core/plugins/mitt";
import { computed, onMounted, ref } from "vue";
import FormIcon from "@common@/components/form/FormIcon.vue";
import FormModalLocal from "@common@/modals/FormModalLocal.vue";
import { $getDisplayValue } from "../../../metronic/core/helpers/utility";

const props = defineProps({
    id: {
        type: String,
        required: true,
    },
    title: {
        type: String,
        default: "",
    },
    mode: {
        type: String,
        default: null,
    },
    fields: {
        type: Array,
        required: true,
        default: [],
    },
    listFields: {
        type: Array,
        required: true,
        default: [],
    },
    size: {
        type: String,
        default: "modal-lg",
    },
});

const formId = ref("sub-item-modal");

const entry = ref({});

const parent = ref({});

const entries = ref([]);

const columns = computed(() => {
    return props.listFields || [];
});

const emit = defineEmits(["update:modelValue"]);

const onSubmit = (e) => {
    if (e.id === null) {
        entries.value.push(e);
    } else {
        const index = entries.value.findIndex((i) => i.id === e.id);
        entries.value[index] = e;
    }
    emit("update:modelValue", entries.value);
};

const closeModal = () => {
    emitter.emit("hide-modal-container", { id: props.id });
};

const initSubFormModal = (id) => {
    let e = _.cloneDeep(entry.value);
    if (id) {
        e = _.cloneDeep(entries.value.find((i) => i.id === id));
    }
    emitter.emit("show-form-modal-local", {
        id: formId.value,
        title: id ? "Update Item" : "New Item",
        fields: props.fields,
        entry: e,
        parent: parent.value,
        mode: id ? "edit" : "create",
    });
};

const removeItem = (e) => {
    const c = confirm("Are you sure you want to delete this item?");
    if (!c) {
        return;
    }
    const index = entries.value.findIndex((i) => i.id === e.id);
    entries.value.splice(index, 1);
    emit("update:modelValue", entries.value);
};

onMounted(() => {
    emitter.on("show-form-modal-list", (payload) => {
        entries.value = payload.entries;
        entry.value = payload.entry;
        parent.value = payload.parent;
        emitter.emit("show-modal-container", payload);
    });
});
</script>

<style scoped></style>
