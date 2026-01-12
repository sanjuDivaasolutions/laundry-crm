<template>
    <div :class="containerFullWidth ? 'w-100' : ''">
        <FormLabel
            v-if="label"
            :name="name"
            :label="label"
            :required="required"
        />
        <input
            :ref="name"
            :name="name"
            :type="'file'"
            class="form-control form-control-solid"
            @change="emitUpdate($event)"
            :required="required"
        />
    </div>
</template>

<script>
import { defineComponent } from "vue";
import emitter from "@/core/plugins/mitt";
import ApiService from "@/core/services/ApiService";
import { $catchResponse } from "@/core/helpers/utility";

export default defineComponent({
    name: "FormFileSingle",
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
        readonly: {
            type: Boolean,
            default: false,
        },
        updateEvent: {
            type: String,
            default: null,
        },
        modelValue: {
            type: [String, Number],
            default: "",
        },
        endpoint: {
            type: String,
            default: null,
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
        const emitUpdate = (value) => {
            if (props.endpoint) {
                const params = {
                    size: 100,
                    model_id: null,
                    collection_name: "project_document",
                };
                const formData = new FormData();
                formData.append("file", value.target.files[0]);
                formData.append("model_id", props.id);
                formData.append("collection_name", "project_document");
                formData.append("size", 100);
                const config = {
                    headers: {
                        "content-type": "multipart/form-data",
                    },
                };
                ApiService.upload(props.endpoint, formData, config)
                    .then((response) => {
                        updateModelValue(response);
                    })
                    .catch((error) => {
                        $catchResponse(error);
                    });
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

        return {
            emitUpdate,
        };
    },
});
</script>

<style scoped></style>
