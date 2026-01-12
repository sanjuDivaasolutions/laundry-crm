<template>
    <div :class="containerFullWidth ? 'w-100' : ''">
        <FormLabel
            v-if="label"
            :name="name"
            :label="label"
            :required="required"
        />
        <ckeditor
            v-if="false"
            :editor="editor"
            @input="emitUpdate($event)"
            :value="modelValue"
            :disabled="readonly"
            :config="editorConfig"
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

<script setup>
import { computed, ref } from "vue";
import emitter from "@/core/plugins/mitt";
import ClassicEditor from "@ckeditor/ckeditor5-build-classic";

// Props
const props = defineProps({
    name: { type: String, required: true },
    type: { type: String, default: "text" },
    required: { type: Boolean, default: false },
    readonly: { type: Boolean, default: false },
    updateEvent: { type: String, default: null },
    modelValue: { type: [String, Number], default: "" },
    label: { type: String, default: null },
    placeholder: { type: String, default: "" },
    autocomplete: { type: String, default: "random-string" },
    icon: { type: String, default: undefined },
    min: { type: [String, Number], default: undefined },
    max: { type: [String, Number], default: undefined },
    step: { type: [String, Number], default: undefined },
    containerFullWidth: { type: Boolean, default: false },
});

const editorConfig = {
    toolbar: ["bold", "italic", "link", "undo", "redo"],
};

// Emits
const emit = defineEmits(["update:modelValue", "focus", "blur"]);

// Reactive Variables
const editor = ref(ClassicEditor);

// Computed Properties
const placeholderText = computed(() => {
    return props.placeholder ? props.placeholder : props.label;
});

// Event Handlers
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
</script>

<style scoped></style>
