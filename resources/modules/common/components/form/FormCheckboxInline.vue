<template>
    <div class="d-flex align-items-center h-100">
        <div class="form-check">
            <input
                :id="name"
                class="form-check-input"
                type="checkbox"
                :checked="modelValue"
                @change="updateValue($event)"
            />
            <label class="form-check-label" :for="name">
                {{ label }}
            </label>
        </div>
    </div>
</template>

<script>
import emitter from "@/core/plugins/mitt";
export default {
    name: "FormCheckboxInline",
    props: {
        name: {
            type: String,
            required: true,
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
            type: [String, Number, Array, Boolean],
            default: null,
        },
        label: {
            type: String,
            default: null,
        },
        hideLabel: {
            type: Boolean,
            default: false,
        },
    },
    methods: {
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
};
</script>

<style lang="scss"></style>
