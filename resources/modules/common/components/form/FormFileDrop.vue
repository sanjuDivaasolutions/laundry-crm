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
  -  *  Last modified: 29/01/25, 11:05 am
  -  *  Written by Chintan Bagdawala, 2025.
  -  */
  -->

<template>
    <div :class="containerFullWidth ? 'w-100' : ''">
        <div class="d-flex justify-content-between">
            <FormLabel
                v-if="label"
                :name="name"
                :label="computedLabel"
                :required="required"
            />
            <FormButton
                v-if="
                    totalFilesAllowed > 0 && images.length < totalFilesAllowed
                "
                @click="initUpload"
                color="link"
                size="sm"
                icon="feather:plus"
                :loading="isLoading"
                >Add Files</FormButton
            >
        </div>
        <CardContainer
            :hide-header="true"
            :hide-footer="true"
            body-padding="no_padding"
        >
            <template #body>
                <div class="d-flex min-h-100px border rounded flex-column p-5">
                    <FormButton
                        v-if="!images && totalFilesAllowed === 1"
                        @click="initUpload"
                        color="danger"
                        size="sm"
                        icon="feather:upload"
                        :loading="isLoading"
                        >Upload
                    </FormButton>
                    <div class="row">
                        <div class="col-md-3 mb-3" v-for="image in images">
                            <div
                                class="d-flex flex-column justify-content-center border rounded"
                            >
                                <div
                                    class="overflow-auto d-flex justify-content-center pt-3"
                                    :class="{
                                        'h-200px': isImage(image),
                                    }"
                                >
                                    <img
                                        v-if="isImage(image)"
                                        class="rounded"
                                        :src="
                                            image.thumbnail ||
                                            image.preview_thumbnail ||
                                            image.original_url ||
                                            image.url
                                        "
                                        alt=""
                                    />
                                    <div
                                        v-else
                                        class="d-flex justify-content-center align-items-center h-100"
                                    >
                                        <div class="text-center">
                                            <a
                                                :href="image.url"
                                                target="_blank"
                                            >
                                                <FormIcon
                                                    icon="feather:file"
                                                    height="4rem"
                                                    width="4rem"
                                                />
                                                <div class="text-muted">
                                                    {{ image.name }}
                                                </div>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <FormButton
                                    v-if="image"
                                    @click="initRemove(image.id)"
                                    color="link"
                                    size="sm"
                                    icon="feather:trash"
                                    :loading="isLoading"
                                    >Remove
                                </FormButton>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </CardContainer>
    </div>
</template>

<script>
import { computed, defineComponent, ref } from "vue";
import ApiService from "@/core/services/ApiService";
import { $confirmDelete, $toastError } from "@/core/helpers/utility";
import emitter from "@/core/plugins/mitt";
import FormIcon from "@common@/components/form/FormIcon.vue";

export default defineComponent({
    name: "FormFileDrop",
    components: { FormIcon },
    props: {
        id: {
            type: String,
            default: null,
        },
        name: {
            type: String,
            required: true,
        },
        required: {
            type: Boolean,
            default: false,
        },
        modelValue: {
            type: Array,
            default() {
                return [];
            },
        },
        updateEvent: {
            type: String,
            default: null,
        },
        endpoint: {
            type: String,
            default: "media-upload",
        },
        config: {
            type: Object,
            default() {
                return {};
            },
        },
        label: {
            type: String,
            default: null,
        },
        containerFullWidth: {
            type: Boolean,
            default: false,
        },
    },
    setup(props, { emit }) {
        const isLoading = ref(false);

        const images = computed(() => {
            return _.get(props, "modelValue", []);
        });

        const totalFilesAllowed = computed(() => {
            return _.get(props, "config.total_files_allowed", 1);
        });

        const computedLabel = computed(() => {
            return props.label + " (Max " + totalFilesAllowed.value + " files)";
        });

        const emitUpdate = async (value) => {
            if (props.endpoint) {
                isLoading.value = true;
                const files = value.target.files;
                const uploaded = _.get(props, "modelValue", []);
                if (files.length > totalFilesAllowed.value) {
                    $toastError(
                        `You can only upload a maximum of ${totalFilesAllowed.value} files`
                    );
                    isLoading.value = false;
                    return;
                }
                for (let i = 0; i < files.length; i++) {
                    const formData = new FormData();
                    formData.append("file", files[i]);
                    formData.append(
                        "model_id",
                        _.get(props, "modelId", 0).toString()
                    );
                    formData.append(
                        "collection_name",
                        _.get(props, "config.collection")
                    );
                    formData.append(
                        "size",
                        _.get(props, "config.size", 100).toString()
                    );
                    const config = {
                        headers: {
                            "content-type": "multipart/form-data",
                        },
                    };
                    const response = await ApiService.upload(
                        props.endpoint,
                        formData,
                        config
                    );
                    const uploadedFile = _.get(response, "data.data.0", null);
                    if (uploadedFile) {
                        uploaded.push(uploadedFile);
                    }
                }
                updateModelValue(uploaded);
                isLoading.value = false;
            } else {
                updateModelValue(value);
            }
        };

        const updateModelValue = (value) => {
            emit("update:modelValue", value);
            if (props.updateEvent) {
                emitter.emit(props.updateEvent, {
                    id: props.id,
                    value: value,
                });
            }
        };

        const initUpload = () => {
            //create a virtual input file element and click it
            const input = document.createElement("input");
            input.type = "file";
            input.multiple = totalFilesAllowed.value > 1;
            input.onchange = (e) => {
                emitUpdate(e);
            };
            input.click();
        };

        const initRemove = (id) => {
            const c = $confirmDelete();
            c.then((confirmed) => {
                if (confirmed) {
                    const updatedImages = images.value.filter((image) => {
                        return image.id !== id;
                    });
                    updateModelValue(updatedImages);
                }
            });
        };

        const isImage = (image) => {
            const mimeType = _.get(image, "mime_type", "");
            return mimeType.includes("image");
        };

        return {
            computedLabel,
            images,
            emitUpdate,
            initUpload,
            initRemove,
            isLoading,
            isImage,
            totalFilesAllowed,
        };
    },
});
</script>
