<template>
    <div :class="containerFullWidth ? 'w-100' : ''">
        <FormLabel
            v-if="label"
            :name="name"
            :label="label"
            :required="required"
        />
        <input
            :name="name"
            type="number"
            class="form-control form-control-solid"
            :placeholder="placeholderText"
            :autocomplete="autocomplete"
            :value="modelValue"
            @input="emitUpdate($event.target.value)"
            @focus="emitFocus"
            @blur="emitBlur"
            :required="required"
            :readonly="readonly"
            :min="min"
            :max="max"
            :step="step"
        />
    </div>
</template>

<script>
import { computed, defineComponent, ref } from "vue";
import emitter from "@/core/plugins/mitt";

export default defineComponent({
    name: "FormDecimal",
    props: {
        id: {
            type: String,
            default: null,
        },
        name: {
            type: String,
            required: true,
        },
        type: {
            type: String,
            default: "text",
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
        label: {
            type: String,
            default: null,
        },
        placeholder: {
            type: String,
            default: "",
        },
        autocomplete: {
            type: String,
            default: "new-password",
        },
        icon: {
            type: String,
            default: undefined,
        },
        min: {
            type: [String, Number],
            default: undefined,
        },
        max: {
            type: [String, Number],
            default: undefined,
        },
        step: {
            type: [String, Number],
            default: "0.00",
        },
        containerFullWidth: {
            type: Boolean,
            default: false,
        },
    },
    setup(props, { emit }) {
        const inputValue = ref(props.modelValue);

        const placeholderText = computed(() => {
            return props.placeholder ? props.placeholder : props.label;
        });

        const updateValue = (event) => {
            inputValue.value = event.target.value;
            emit("input", inputValue.value);
        };

        const emitFocus = () => {
            emit("focus", props.name);
        };

        const emitBlur = () => {
            emit("blur");
        };

        const emitUpdate = (value) => {
            emit("update:modelValue", value);
            if (props.updateEvent) {
                emitter.emit(props.updateEvent, { id: props.id, value: value });
            }
        };

        return {
            inputValue,
            placeholderText,
            updateValue,
            emitFocus,
            emitBlur,
            emitUpdate,
        };
    },
});
</script>

<style scoped></style>
