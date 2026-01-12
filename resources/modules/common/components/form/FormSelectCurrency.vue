<template>
    <FormLabel v-if="label" :name="name" :label="label" :required="required">
        <template #link
            ><FormCurrencyControl
                :source-value="sourceValue"
                :source-code="sourceCode"
                :target-value="targetValue"
                :target-code="targetCode"
                @saved="updateCurrencyRate"
        /></template>
    </FormLabel>
    <Multiselect
        :model-value="modelValue"
        @update:modelValue="emitUpdate"
        :placeholder="getPlaceholder"
        :options="filteredOptions"
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
import FormCurrencyControl from "@common@/components/form/FormCurrencyControl.vue";
import emitter from "@/core/plugins/mitt";
import { useOptionStore } from "@common@/components/optionStore";
export default {
    name: "FormSelectCurrency",
    components: {
        Multiselect,
        FormCurrencyControl,
    },
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
        updateEvent: {
            type: String,
            default: null,
        },
        currency: {
            type: Object,
            required: true,
            default: () => ({
                sourceValue: 1,
                sourceCode: "USD",
                targetValue: 1,
                targetCode: "USD",
            }),
        },
        currencyRate: {
            type: Number,
            default: 1,
        },
        currencyRateField: {
            type: String,
            default: "currency_rate",
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
            fetchedOptions: [],
        };
    },
    computed: {
        sourceValue() {
            return this.currency ? this.currency.sourceValue : 1;
        },
        sourceCode() {
            return this.currency ? this.currency.sourceCode : "USD";
        },
        targetValue() {
            return this.currency ? this.currency.targetValue : 1;
        },
        targetCode() {
            return this.currency ? this.currency.targetCode : "USD";
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
            if (this.endpoint) {
                return this.fetchedOptions;
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
            const store = useOptionStore();
            const existing = store.getOption(this.endpoint);
            if (existing) {
                this.fetchedOptions = existing;
            } else {
                const response = await this.fetchOptionsFromServer();
                this.fetchedOptions = response.data.data;
                store.setOption(this.endpoint, response.data.data);
            }
        },
        async fetchOptionsFromServer() {
            return await ApiService.get(`options/${this.endpoint}`);
        },
        updateCurrencyRate(rate) {
            this.$emit("update:currencyRate", rate);
            emitter.emit(`${this.currencyRateField}-update`, rate);
            this.fetchOptions();
        },
        emitUpdate(value) {
            const c = _.cloneDeep(value);
            c.sourceCode = $headMeta("default_currency_code");
            c.sourceValue = $headMeta("default_currency_id");
            c.targetCode = c.code;
            c.targetValue = c.rate;
            this.$emit("update:modelValue", c);
            if (this.updateEvent) {
                emitter.emit(this.updateEvent, {
                    obj: c,
                    currencyRateField: this.currencyRateField,
                });
            }
        },
    },
    created() {
        if (this.endpoint) {
            this.fetchOptions();
        }
    },
};
</script>

<style lang="scss"></style>
