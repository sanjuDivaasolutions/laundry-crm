<template>
    <div :class="containerFullWidth ? 'w-100' : ''">
        <FormLabel
            v-if="label"
            :name="name"
            :label="label"
            :required="required"
            class="mb-3"
        />
        <label
            class="form-check form-switch form-switch-md form-check-custom form-check-solid d-flex align-items-center"
            :for="id"
        >
            <input
                class="form-check-input"
                type="checkbox"
                :name="name"
                :id="id"
                :value="modelValue"
                @input="emitUpdate($event.target.value)"
                :checked="modelValue"
            />
            <span class="form-check-label fw-semobold text-muted">{{
                label
            }}</span>
        </label>
    </div>
</template>

<script>
import { computed, defineComponent, ref } from "vue";
import emitter from "@/core/plugins/mitt";

export default defineComponent({
    name: "FormSwitch",
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
            default: "checkbox",
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
            const updated = value !== "true";
            emit("update:modelValue", updated);
            if (props.updateEvent) {
                emitter.emit(props.updateEvent, {
                    id: props.id,
                    value: updated,
                });
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
