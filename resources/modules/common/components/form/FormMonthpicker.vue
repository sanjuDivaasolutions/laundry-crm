<template>
    <div>
        <FormLabel
            v-if="label"
            :name="name"
            :label="label"
            :required="required"
        />
        <datepicker
            :name="name"
            class="form-control form-control-solid"
            :placeholder="placeholderText"
            :autocomplete="autocomplete"
            :value="modelValue"
            @update:model-value="updateValue"
            :starting-view="`year`"
            :minimum-view="`month`"
            :required="required"
            :readonly="readonly"
            :lowerLimit="min"
            :upperLimit="max"
            :clearable="true"
            input-format="MM/yyyy"
        />
    </div>
</template>

<script>
import { computed, defineComponent, onMounted } from "vue";
import Datepicker from "vue3-datepicker";
import moment from "moment";

export default defineComponent({
    name: "FormMonthpicker",
    components: {
        Datepicker,
    },
    props: {
        name: {
            type: String,
            required: true,
        },
        required: {
            type: Boolean,
            default: false,
        },
        readonly: {
            type: Boolean,
            default: false,
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
            type: [Date],
            default: null,
        },
        max: {
            type: [Date],
            default: null,
        },
    },
    setup(props, { emit }) {
        const inputValue = computed(() => props.modelValue);

        const placeholderText = computed(() => {
            return props.placeholder ? props.placeholder : props.label;
        });

        const updateValue = (event) => {
            //TODO change the format below to moment dynamic format (get value from meta)
            const value = moment(event).format("MM/YYYY");
            emit("update:modelValue", value);
        };

        onMounted(() => {
            if (props.modelValue) {
                const value = props.modelValue;
                emit("update:modelValue", null);
                setTimeout(() => {
                    emit("update:modelValue", value);
                }, 50);
            }
        });

        return {
            inputValue,
            placeholderText,
            updateValue,
        };
    },
});
</script>

<style scoped></style>
