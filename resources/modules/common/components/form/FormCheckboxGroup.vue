<template>
    <FormLabel v-if="label" :name="name" :label="label" :required="required" />
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <a href="#" @click.prevent="checkAll">Check all</a
            >&nbsp;&nbsp;/&nbsp;&nbsp;<a href="#" @click.prevent="uncheckAll"
                >Uncheck all</a
            >
        </div>
        <div>
            <input
                type="text"
                class="form-control"
                placeholder="Search..."
                v-model="filters.s"
            />
        </div>
    </div>
    <div style="max-height: 500px; overflow-y: auto; overflow-x: hidden">
        <div class="row">
            <div class="col-4" v-for="s in filteredOptions">
                <span class="d-flex align-items-center"
                    ><input
                        type="checkbox"
                        :checked="modelValue.indexOf(s.id) !== -1"
                        :value="s.id"
                        @change="updateValue($event.target.value)"
                    />&nbsp;{{ s.title }}</span
                >
            </div>
        </div>
    </div>
</template>

<script>
import ApiService from "@/core/services/ApiService";
import { useOptionStore } from "@common@/components/optionStore";
import emitter from "@/core/plugins/mitt";
export default {
    name: "FormSelectSingle",
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
        updateValue(value) {
            const updated = _.cloneDeep(this.modelValue);
            const index = updated.indexOf(Number(value));
            if (index === -1) {
                updated.push(value);
            } else {
                updated.splice(index, 1);
            }
            this.emitModelValue(updated);
        },
        emitModelValue(values) {
            this.$emit("update:modelValue", values);
            if (this.updateEvent) {
                emitter.emit(this.updateEvent, { value: values });
            }
        },
        checkAll() {
            this.emitModelValue(this.fetchedOptions.map((o) => o.id));
        },
        uncheckAll() {
            this.emitModelValue([]);
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
