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
  -  *  Last modified: 12/12/24, 9:24 am
  -  *  Written by Chintan Bagdawala, 2024.
  -  */
  -->

<template>
    <div>
        <MagicDatatable
            :id="magicDatatableId"
            :table-title="module.tableTitle || module.plural"
            card-body-padding="no_padding"
            card-header-padding="no_padding"
            card-footer-padding="no_padding"
            heading="Filters"
            :filters="filters"
            :columns="columns"
            :module="module"
            pagination-position="bottom"
            :save-state="saveState"
            :bulk-selection="module.bulkSelection || false"
            :bulk-actions="module.bulkActions || []"
            :disable-bulk-remove="module.disableBulkRemove || false"
        />
        <FormModal
            :id="modals.form.id"
            :title="formModalTitle"
            :fields="formFields"
            :mode="formMode"
            :size="modals.form.size"
            :centered="isFormModalCentered"
            :backdrop="isFormModalBackdrop"
            :option-store-value="optionStoreValue"
            :route="modals.form.route"
            :defaults-route="module.defaultsRoute || null"
        >
            <template #modal-body>
                <slot name="form-modal-body"></slot>
            </template>
        </FormModal>
        <ShowModal
            :id="modals.show.id"
            :title="modals.show.showTitle"
            :fields="showFields"
            :route="modals.show.route"
            :size="modals.show.size"
        />
    </div>
</template>

<script setup>
import MagicDatatable from "@/components/magic-datatable/MagicDatatable.vue";
import FormModal from "@common@/modals/FormModal.vue";
import ShowModal from "@common@/modals/ShowModal.vue";
import { computed, onBeforeUnmount, onMounted, ref } from "vue";
import { usePageStore } from "@/stores/page";
import emitter from "@/core/plugins/mitt";
import UploadService from "@/core/services/UploadService";
import { $catchResponse, $toastSuccess } from "@/core/helpers/utility";
import { useOptionStore } from "@common@/components/optionStore";

const props = defineProps({
    indexStore: {
        required: true,
    },
    formStore: {
        required: true,
        default: () => {
            return {
                formFields: [],
                showFields: [],
                modals: {
                    form: {
                        id: null,
                        route: null,
                        size: null,
                        centered: true,
                        createTitle: null,
                        editTitle: null,
                        backdrop: true,
                    },
                    show: {
                        id: null,
                        route: null,
                        size: null,
                        showTitle: null,
                        backdrop: true,
                    },
                },
            };
        },
    },
});

const optionStore = useOptionStore();

const isFormModalCentered = computed(() => {
    const isCentered = props.formStore.modals.form.centered;
    return isCentered !== false;
});

const isFormModalBackdrop = computed(() => {
    const isBackdrop = props.formStore.modals.form.backdrop;
    return isBackdrop !== false;
});

//Index Start
const moduleIndexStore = props.indexStore;
const columns = computed(() => moduleIndexStore.columns);
const module = computed(() => moduleIndexStore.module);
const filters = computed(() => moduleIndexStore.filters);
const saveState = ref(module.value.saveState || false);
const magicDatatableId = ref(`magic-table-${module.value.id}`);
//Index End

//Form Start
const moduleFormStore = props.formStore;
const formFields = computed(() => moduleFormStore.formFields);
const showFields = computed(() => moduleFormStore.showFields);
const modals = computed(() => moduleFormStore.modals);
const formModalTitle = ref(null);
const formMode = ref(null);
const modalEvents = ["perform-item-delete"];
if (module.value.formType === "modal") {
    modalEvents.push(`init-${modals.value.form.id}`);
}
if (module.value.showType === "modal") {
    modalEvents.push(`init-${modals.value.show.id}`);
}
const optionStoreValue = computed(() => {
    return optionStore.hasOption(module.value.id) ? module.value.id : null;
});
//Form End

//Page Start
const page = usePageStore();
//Page End

onMounted(() => {
    page.setConfigs(moduleIndexStore.listPageConfigs);
    if (module.value.formType === "modal") {
        emitter.on(`init-${modals.value.form.id}`, async (payload) => {
            formMode.value = payload.id ? "edit" : "create";
            if (formMode.value === "edit") {
                formModalTitle.value = modals.value.form.editTitle;
                await moduleFormStore.loadEditData(payload.id);
            } else {
                formModalTitle.value = modals.value.form.createTitle;
                moduleFormStore.resetEntry();
            }
            emitter.emit("show-form-modal", {
                id: modals.value.form.id,
                entry: moduleFormStore.entry,
            });
        });
    }
    if (module.value.showType === "modal") {
        emitter.on(`init-${modals.value.show.id}`, async (e) => {
            await moduleFormStore.loadShowData(e.id);
            emitter.emit("show-show-modal", {
                id: modals.value.show.id,
                entry: moduleFormStore.entry,
            });
        });
    }
    emitter.on("perform-item-delete", async (id) => {
        await moduleIndexStore.removeItem(id);
        emitter.emit("refresh-magic-table-data", {
            id: magicDatatableId.value,
        });
    });
    if (module.value.import && module.value.import.enabled) {
        emitter.on("trigger-import-data", async (data) => {
            const fileInput = document.createElement("input");
            fileInput.type = "file";
            fileInput.style.display = "none";
            document.body.appendChild(fileInput);
            fileInput.click();
            fileInput.addEventListener("change", function () {
                if (!this.files.length) {
                    return;
                }
                document.body.removeChild(fileInput);
                UploadService.handleUpload(module.value.import.endpoint, {
                    file: this.files[0],
                })
                    .then((res) => {
                        $toastSuccess(res.data.message);
                        emitter.emit("refresh-magic-table-data", {
                            id: magicDatatableId.value,
                        });
                    })
                    .catch((err) => {
                        $catchResponse(err);
                    })
                    .finally(() => {
                        //fileInput.removeEventListener("change");
                    });
            });
        });
    }
    emitter.on("form-modal-refresh-magic-table-data", () => {
        emitter.emit("refresh-magic-table-data", {
            id: magicDatatableId.value,
        });
    });
});
onBeforeUnmount(() => {
    emitter.off(`perform-item-delete`);
    emitter.off(`init-${modals.value.form.id}`);
    emitter.off(`init-${modals.value.show.id}`);
    emitter.off("trigger-import-data");
    emitter.off("form-modal-refresh-magic-table-data");
    emitter.emit("kill-events-modal-container");
});
</script>

<style scoped></style>
