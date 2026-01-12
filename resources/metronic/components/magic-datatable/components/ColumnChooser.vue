<template>
    <ModalContainer
        :id="modal.id"
        :centered="false"
        :narrow-padding="true"
        size="''"
        title="Column Chooser"
    >
        <template #body>
            <div class="overflow-container">
                <div
                    v-for="c in chooserColumns"
                    class="row d-flex justify-content-between align-items-center mb-3 py-2 border border-1 border-secondary rounded"
                >
                    <div class="col d-flex justify-content-center">
                        <div
                            class="form-check form-switch fv-row cursor-pointer"
                        >
                            <input
                                :id="c.title"
                                v-model="c.visible"
                                class="form-check-input w-45px h-30px"
                                type="checkbox"
                            />
                            <label
                                :for="c.title"
                                class="form-check-label"
                            ></label>
                        </div>
                    </div>
                    <div class="col d-flex justify-content-start">
                        <strong>{{ $t(c.title) }}</strong>
                    </div>
                </div>
            </div>
            <div class="row mt-5">
                <div class="col-12 d-flex justify-content-between w-100 px-5">
                    <div class="d-flex justify-content-start">
                        <FormButton
                            :config="{
                                icon: 'feather:save',
                                color: 'primary',
                                size: 'sm',
                            }"
                            class="me-3"
                            color="primary"
                            icon="feather:save"
                            size="sm"
                            @click="handleSave"
                        >
                            Save
                        </FormButton>
                        <FormButton
                            :config="{
                                icon: 'feather:x',
                                color: 'secondary',
                                size: 'sm',
                            }"
                            color="secondary"
                            icon="feather:x"
                            size="sm"
                            @click="handleCancel"
                        >
                            Cancel
                        </FormButton>
                    </div>
                    <div class="d-flex justify-content-end">
                        <FormButton
                            :config="{
                                icon: 'feather:slash',
                                color: 'danger',
                                size: 'sm',
                            }"
                            color="danger"
                            icon="feather:slash"
                            size="sm"
                            @click="handleReset"
                        >
                            Reset
                        </FormButton>
                    </div>
                </div>
            </div>
        </template>
    </ModalContainer>
</template>

<script setup>
import ModalContainer from "@common@/modals/ModalContainer.vue";
import { onBeforeUnmount, onMounted, ref } from "vue";
import emitter from "@/core/plugins/mitt";
import FormButton from "@common@/components/form/FormButton.vue";

const props = defineProps({
    columns: {
        type: Array,
        required: true,
    },
});

const emit = defineEmits(["save"]);

const chooserColumns = ref([]);

const modal = ref({
    id: "column-chooser-modal",
    event: "open-column-chooser-modal",
    centered: false,
});

const initColumnChooserModal = () => {
    setupColumns();
    const payload = {
        id: modal.value.id,
    };
    emitter.emit(`show-modal-container`, payload);
};

const handleSave = () => {
    const payload = {
        id: modal.value.id,
    };
    emitter.emit(`hide-modal-container`, payload);
    emit("save", chooserColumns.value);
};

const handleReset = () => {
    setupColumns(true);
};

const handleCancel = () => {
    const payload = {
        id: modal.value.id,
    };
    emitter.emit(`hide-modal-container`, payload);
};

const setupColumns = (reset = false) => {
    chooserColumns.value = [];
    props.columns.forEach((c) => {
        const column = _.cloneDeep(c);
        column.visible = reset ? true : c.visible !== false;
        chooserColumns.value.push(column);
    });
};

onBeforeUnmount(() => {
    emitter.off(modal.value.event);
});

onMounted(() => {
    emitter.on(modal.value.event, initColumnChooserModal);
});
</script>

<style scoped>
.overflow-container {
    overflow-y: auto;
    max-height: 500px;
    padding: 0 15px;
}
</style>
