<template>
    <Multiselect
        :value="value"
        @change="$emit('change', $event)"
        :placeholder="getPlaceholder"
        :options="options"
        :value-prop="idValue"
        :label="labelValue"
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
export default {
    name: "FormSelectMultiple",
    components: {
        Multiselect,
    },
    props: {
        name: {
            type: String,
            required: true,
        },
        options: {
            type: Array,
            required: true,
        },
        required: {
            type: Boolean,
            default: false,
        },
        disabled: {
            type: Boolean,
            default: false,
        },
        value: {
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
        loading: {
            type: Boolean,
            default: false,
        },
    },
    computed: {
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
};
</script>

<style scoped></style>
