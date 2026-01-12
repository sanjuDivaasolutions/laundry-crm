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
  -  *  Last modified: 07/01/25, 11:41 am
  -  *  Written by Chintan Bagdawala, 2025.
  -  */
  -->

<template>
    <div
        class="modal fade"
        :id="`${id}-container`"
        :ref="`${id}-container`"
        tabindex="-1"
        aria-hidden="true"
        :data-bs-backdrop="getBackdrop"
    >
        <div class="modal-dialog" :class="getModalClasses">
            <div class="modal-content rounded">
                <div class="modal-header py-4">
                    <h2 class="fw-bold">{{ title }}</h2>
                    <div @click="closeModal" class="cursor-pointer">
                        <FormIcon
                            icon="feather:x"
                            class="modal-close-button-icon"
                        />
                    </div>
                </div>
                <div
                    class="modal-body"
                    :class="!narrowPadding ? 'py-10 px-lg-17' : ''"
                >
                    <slot name="body"></slot>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, onMounted, ref } from "vue";
import emitter from "@/core/plugins/mitt";
import { Modal } from "bootstrap";
import FormIcon from "@common@/components/form/FormIcon.vue";

const props = defineProps({
    id: {
        type: String,
        required: true,
    },
    title: {
        type: String,
        default: null,
    },
    centered: {
        type: Boolean,
        default: true,
    },
    backdrop: {
        type: Boolean,
        default: true,
    },
    allowClose: {
        type: Boolean,
        default: true,
    },
    scrollable: {
        type: Boolean,
        default: false,
    },
    size: {
        type: String,
        default: "modal-lg",
    },
    narrowPadding: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(["close"]);

const getBackdrop = computed(() => {
    return props.backdrop === true ? "static" : null;
});

const getModalClasses = computed(() => {
    let classes = [];
    if (props.centered) {
        classes.push("modal-dialog-centered");
    }
    if (props.scrollable) {
        classes.push("modal-dialog-scrollable");
    }
    if (props.size) {
        classes.push(props.size);
    }
    return classes;
});

const modal = ref(null);

const closeModal = () => {
    modal.value.hide();
    emit("close");
    emitter.emit("cancel-modal-container");
};

onMounted(() => {
    const el = document.getElementById(props.id + "-container");
    if (!el) {
        console.error(
            `ModalContainer.vue: Element with id ${props.id}-container not found!`
        );
        return;
    }
    modal.value = new Modal(el);
    emitter.on("show-modal-container", (payload) => {
        if (payload.id !== props.id) {
            return;
        }
        modal.value.show();
    });
    emitter.on("hide-modal-container", (payload) => {
        if (payload.id !== props.id) {
            return;
        }
        modal.value.hide();
    });
    emitter.on("kill-events-modal-container", (payload) => {
        turnOffModalEvents(payload);
    });
});

const turnOffModalEvents = (payload) => {
    emitter.off("show-modal-container");
    emitter.off("hide-modal-container");
};
</script>

<style scoped>
.modal-close-button-icon {
    font-size: 2rem;
}
.modal.fade .modal-dialog.modal-dialog-centered {
    transition: transform 0.3s ease-in !important;
    transform: translate(0, -50px) !important;
}
</style>
