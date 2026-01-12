<template>
    <FormLabel
        v-if="label && !hideLabel"
        :name="name"
        :label="label"
        :required="required"
    />
    <input
        type="checkbox"
        :checked="modelValue"
        @change="updateValue($event)"
    />
</template>

<script>
import ApiService from "@/core/services/ApiService";
import { useOptionStore } from "@common@/components/optionStore";
import emitter from "@/core/plugins/mitt";
export default {
    name: "FormCheckbox",
    props: {
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
        hideLabel: {
            type: Boolean,
            default: false,
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
    },
    data() {
        return {
            filters: {
                s: null,
            },
            fetchedOptions: [],
        };
    },
    computed: {
        searchStr: {
            get() {
                return this.filters.s;
            },
            set(value) {
                this.filters.s = value;
            },
        },
        filteredLabelValue() {
            return this.labelValue || "label";
            //return this.endpoint ? 'label' : this.labelValue;
        },
        filteredIdValue() {
            return this.idValue || "value";
            //return this.endpoint ? "value" : this.idValue;
        },
        filteredOptions() {
            const str = this.searchStr;
            let options = _.cloneDeep(this.options);
            if (this.endpoint) {
                options = _.cloneDeep(this.fetchedOptions);
            }
            if (str) {
                options = options.filter((o) => {
                    return o[this.filteredLabelValue]
                        .toLowerCase()
                        .includes(str.toLowerCase());
                });
            }
            return options;
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
                const v = _.get(this.parent, this.dependentField);
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
                const v = _.get(this.parent, param.field);
                if (v) {
                    params[param.parent] = v;
                }
            });
            return await ApiService.get(`options/${this.endpoint}`, {
                params: params,
            });
        },
        updateValue(event) {
            this.emitModelValue(event.target.checked);
        },
        emitModelValue(value) {
            this.$emit("update:modelValue", value);
            if (this.updateEvent) {
                emitter.emit(this.updateEvent, { value: value });
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
    },
};
</script>

<style lang="scss"></style>
