<template>
    <div :class="containerFullWidth ? 'w-100' : ''">
        <FormLabel
            v-if="label"
            :name="name"
            :label="label"
            :required="required"
        />
        <textarea
            :name="name"
            class="form-control form-control-solid"
            :placeholder="placeholderText"
            :autocomplete="autocomplete"
            :value="modelValue"
            @input="emitUpdate($event.target.value)"
            @focus="emitFocus"
            @blur="emitBlur"
            :required="required"
            :readonly="readonly"
        ></textarea>
    </div>
</template>

<script>
import { computed, defineComponent, ref } from "vue";
import emitter from "@/core/plugins/mitt";

export default defineComponent({
    name: "FormInput",
    props: {
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
            default: "random-string",
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
            default: undefined,
        },
        containerFullWidth: {
            type: Boolean,
            default: false,
        },
    },
    setup(props, { emit }) {
        const inputValue = ref(props.value);

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

        const emitUpdate = (value) => {
            emit("update:modelValue", value);
            if (props.updateEvent) {
                emitter.emit(props.updateEvent, value);
            }
        };

        const emitBlur = () => {
            emit("blur");
        };

        return {
            inputValue,
            placeholderText,
            updateValue,
            emitUpdate,
            emitFocus,
            emitBlur,
        };
    },
});
</script>

<style scoped></style>
