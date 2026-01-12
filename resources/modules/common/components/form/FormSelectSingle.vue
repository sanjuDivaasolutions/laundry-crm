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
  -  *  Last modified: 05/02/25, 6:01 pm
  -  *  Written by Chintan Bagdawala, 2025.
  -  */
  -->

<template>
    <FormLabel v-if="label" :name="name" :label="label" :required="required" />
    <Multiselect
        :model-value="modelValue"
        @update:modelValue="emitUpdate($event)"
        :placeholder="getPlaceholder"
        :options="filteredOptions"
        :track-by="filteredLabelValue"
        :value-prop="filteredIdValue"
        :label="filteredLabelValue"
        :mode="mode"
        :searchable="true"
        :object="object"
        :open-direction="openDirection"
        :hide-selected="hideSelected"
        :required="required"
        :disabled="disabled"
    />
</template>

<script>
import Multiselect from "@vueform/multiselect";
import ApiService from "@/core/services/ApiService";
import { useOptionStore } from "@common@/components/optionStore";
import emitter from "@/core/plugins/mitt";

export default {
    name: "FormSelectSingle",
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
        options: {
            type: Array,
            default: () => [],
        },
        endpoint: {
            type: String,
            default: null,
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
        mainEntry: {
            type: Object,
            default: () => {},
        },
        entry: {
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
            default: "single",
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
            default: "id",
        },
        labelValue: {
            type: String,
            default: "name",
        },
        label: {
            type: String,
            default: null,
        },
        object: {
            type: Boolean,
            default: true,
        },
        dependentFieldLevel: {
            type: String,
            default: "same",
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
    },
    data() {
        return {
            fetchedOptions: [],
            indexOptions: [],
        };
    },
    computed: {
        filteredLabelValue() {
            return this.labelValue || "label";
            //return this.endpoint ? 'label' : this.labelValue;
        },
        filteredIdValue() {
            return this.idValue || "value";
            //return this.endpoint ? "value" : this.idValue;
        },
        filteredOptions() {
            if (this.endpoint) {
                return this.fetchedOptions;
            }
            if (this.indexOptions) {
                return this.indexOptions;
            }
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
    },
    methods: {
        async fetchOptions() {
            if (this.dependentField) {
                const obj =
                    this.dependentFieldLevel !== "same"
                        ? this.mainEntry
                        : this.parent;
                const v = _.get(obj, this.dependentField);
                if (!v) {
                    return [];
                }
            }
            const forceFetchFromServer = this.endpointFilterParams.length > 0;
            const store = useOptionStore();
            let existing = null;
            if (!forceFetchFromServer) {
                existing = store.getOption(this.endpoint);
            }
            if (existing) {
                this.fetchedOptions = existing;
            } else {
                //store.setOption(this.endpoint, []);
                const response = await this.fetchOptionsFromServer();
                this.fetchedOptions = response.data.data;
                if (response.data.data && !forceFetchFromServer) {
                    store.setOption(this.endpoint, response.data.data);
                }
            }
        },
        async fetchOptionsFromServer() {
            const params = {};
            this.endpointFilterParams.forEach((param) => {
                const obj =
                    this.dependentFieldLevel !== "same"
                        ? this.mainEntry
                        : this.parent;
                const v = _.get(obj, param.field);
                if (v) {
                    params[param.parent] = v;
                }
            });
            return await ApiService.get(`options/${this.endpoint}`, {
                params: params,
            });
        },
        async fetchIndexOptions(endpoint, params) {
            const response = await ApiService.get(`options/${endpoint}`, {
                params: params,
            });
            this.indexOptions = response.data.data;
        },
        emitUpdate(value) {
            this.$emit("update:modelValue", value);
            if (this.updateEvent) {
                emitter.emit(this.updateEvent, {
                    value: value,
                    index: this.index,
                });
            }
        },
    },
    mounted() {
        if (this.endpoint) {
            this.fetchOptions();
        }
        emitter.on("refresh-options", async (data) => {
            if (data.endpoint === this.endpoint) {
                await this.fetchOptions();
            }
        });
        if (this.index !== null && this.endpoint === null) {
            const event = `refresh-options-${this.index}-${this.name}`;
            emitter.on(event, async (params) => {
                const endpoint = params.endpoint;
                const p = params.params;
                await this.fetchIndexOptions(endpoint, p);
            });
        }
    },
    beforeDestroy() {
        if (this.index) {
            emitter.off(`refresh-options-${this.index}`);
        }
    },
};
</script>

<style lang="scss"></style>
