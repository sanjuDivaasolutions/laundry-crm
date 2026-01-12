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
  -  *  Last modified: 13/01/25, 5:52 pm
  -  *  Written by Chintan Bagdawala, 2025.
  -  */
  -->

<template>
    <ModalContainer :id="id" :title="title" :size="size">
        <template #body>
            <slot name="modal-body">
                <FormFields
                    :fields="fields"
                    :entry="entry"
                    :parent="parent"
                    :mode="mode"
                    @submit="onSubmit"
                    @cancel="closeModal"
                />
            </slot>
        </template>
    </ModalContainer>
</template>

<script setup>
import ModalContainer from "@common@/modals/ModalContainer.vue";
import FormFields from "@common@/components/FormFields.vue";
import emitter from "@/core/plugins/mitt";
import { onBeforeUnmount, onMounted, ref } from "vue";

const props = defineProps({
    id: {
        type: String,
        required: true,
    },
    fields: {
        type: Array,
        required: true,
        default: [],
    },
    size: {
        type: String,
        default: "modal-lg",
    },
});

const emit = defineEmits(["submit", "cancel"]);

const entry = ref({});
const parent = ref(null);
const mode = ref(null);
const title = ref("");

const onSubmit = () => {
    const e = _.cloneDeep(entry.value);
    entry.value = {};
    emit("submit", e);
    closeModal();
};

const onCancel = () => {
    emit("cancel");
    closeModal();
};

const closeModal = () => {
    emitter.emit("hide-modal-container", { id: props.id });
};

onMounted(() => {
    emitter.on("show-form-modal-local", (payload) => {
        entry.value = payload.entry;
        parent.value = payload.parent;
        mode.value = payload.mode;
        title.value = payload.title;
        emitter.emit("show-modal-container", payload);
    });
});

onBeforeUnmount(() => {
    emitter.off("show-form-modal-local");
});
</script>

<style scoped></style>
