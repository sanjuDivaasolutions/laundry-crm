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
  -  *  Last modified: 22/01/25, 5:56 pm
  -  *  Written by Chintan Bagdawala, 2025.
  -  */
  -->

<template>
    <div :class="containerFullWidth ? 'w-100' : ''">
        <FormLabel
            v-if="label"
            :name="name"
            :label="label"
            :required="required"
        />
        <Multiselect
            :model-value="modelValue"
            @update:modelValue="updateValue"
            :placeholder="getPlaceholder"
            :options="getOptions"
            :value-prop="idValue"
            :label="labelValue"
            :mode="mode"
            :loading="loading"
            :internal-search="false"
            @search-change="fetchOptions"
            :searchable="true"
            :filter-results="false"
            :close-on-select="closeOnSelect"
            :object="object"
            :open-direction="openDirection"
            :hide-selected="hideSelected"
            :required="required"
            :disabled="disabled"
            :no-options-text="$t('general.fields.noInitialResults')"
            :classes="classes"
        />
    </div>
</template>

<script>
import Multiselect from "@vueform/multiselect";
import ApiService from "@/core/services/ApiService";
import emitter from "@/core/plugins/mitt";

export default {
    name: "FormSelectAjax",
    components: {
        Multiselect,
    },
    props: {
        index: {
            type: Number,
            default: null,
        },
        name: {
            type: String,
            required: true,
        },
        entry: {
            type: Object,
            required: true,
        },
        endpoint: {
            type: String,
            required: true,
        },
        endpointFilterParams: {
            type: Array,
            default: () => [],
        },
        dependentField: {
            type: String,
            default: null,
        },
        updateEvent: {
            type: String,
            default: null,
        },
        parent: {
            type: Object,
            default: () => {},
        },
        required: {
            type: Boolean,
            default: false,
        },
        disabled: {
            type: Boolean,
            default: false,
        },
        modelValue: {
            type: [String, Number, Array],
            default: null,
        },
        mode: {
            type: String,
            default: "multiple",
            validator(value) {
                return ["single", "multiple", "tags"].includes(value);
            },
        },
        closeOnSelect: {
            type: Boolean,
            default: true,
        },
        idValue: {
            type: String,
            default: "value",
        },
        labelValue: {
            type: String,
            default: "label",
        },
        label: {
            type: String,
            default: null,
        },
        object: {
            type: Boolean,
            default: true,
        },
        placeholder: {
            type: String,
            default: "",
        },
        hideSelected: {
            type: Boolean,
            default: false,
        },
        openDirection: {
            type: String,
            default: "bottom",
        },
        loading: {
            type: Boolean,
            default: false,
        },
        containerFullWidth: {
            type: Boolean,
            default: false,
        },
    },
    data() {
        return {
            options: [],
            searchQuery: "",
            debounceTimeout: null,
        };
    },
    computed: {
        getOptions() {
            return this.options;
        },
        getPlaceholder() {
            if (this.placeholder) {
                return this.placeholder;
            }
            if (this.label) {
                return this.label;
            }
            return "";
        },
        classes() {
            return {};
        },
    },
    methods: {
        fetchOptions(e) {
            clearTimeout(this.debounceTimeout);
            if (!e) return;
            // Set a new debounce timeout to fetch options after a short delay
            this.debounceTimeout = setTimeout(() => {
                //this.loading = true;
                const params = {};
                this.endpointFilterParams.forEach((param) => {
                    const v = _.get(this.parent, param.field);
                    if (v) {
                        params[param.parent] = v;
                    }
                });
                params.q = e;
                ApiService.get(`query/${this.endpoint}`, {
                    params: params,
                })
                    .then((response) => {
                        this.options = response.data.data;
                    })
                    .catch((error) => {
                        console.log(error);
                    })
                    .finally(() => {
                        //this.loading = false;
                    });
            }, 500);
        },
        updateValue(value) {
            this.$emit("update:modelValue", value);
            if (this.updateEvent) {
                emitter.emit(this.updateEvent, {
                    value: value,
                    index: this.index,
                });
            } else {
                emitter.emit(`update:${this.name}`, value);
            }
        },
    },
};
</script>

<style scoped></style>
