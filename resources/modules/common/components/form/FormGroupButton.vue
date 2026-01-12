<template>
    <div :class="containerFullWidth ? 'w-100' : ''">
        <FormLabel
            v-if="label"
            :name="name"
            :label="label"
            :required="required"
        />
        <div class="btn-group" role="group" aria-label="First group">
            <button
                v-for="s in buttons"
                type="button"
                class="btn btn-secondary"
                :class="{
                    active: s.value === modelValue,
                }"
                @click="emitUpdate(s.value)"
            >
                {{ s.label }}
            </button>
        </div>
    </div>
</template>

<script>
import { computed, defineComponent, ref } from "vue";
import emitter from "@/core/plugins/mitt";

export default defineComponent({
    name: "FormGroupButton",
    props: {
        id: {
            type: String,
            default: null,
        },
        name: {
            type: String,
            required: true,
        },
        config: {
            type: Object,
            default: () => ({
                buttons: [],
            }),
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
        containerFullWidth: {
            type: Boolean,
            default: false,
        },
    },
    setup(props, { emit }) {
        const buttons = computed(() => _.get(props, "config.buttons", []));

        const inputValue = ref(props.modelValue);
        const emitUpdate = (value) => {
            emit("update:modelValue", value);
            if (props.updateEvent) {
                emitter.emit(props.updateEvent, { id: props.id, value: value });
            }
        };

        return {
            inputValue,
            emitUpdate,
            buttons,
        };
    },
});
</script>

<style scoped></style>
