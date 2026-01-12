<template>
    <div>
        <FormLabel
            v-if="label"
            :name="name"
            :label="label"
            :required="required"
        />
        <VueDatePicker
            :name="name"
            input-class-name="form-control form-control-solid"
            :placeholder="placeholderText"
            :autocomplete="autocomplete"
            :model-value="v"
            @update:model-value="updateValue"
            :auto-apply="true"
            :required="required"
            :readonly="readonly"
            :clearable="true"
            :text-input="true"
            :format="datePickerFormat"
            :enable-time-picker="false"
            :dark="isDarkMode"
        />
    </div>
</template>

<script>
import { computed, defineComponent, onMounted } from "vue";
import VueDatePicker from "@vuepic/vue-datepicker";
import moment from "moment";
import { $headMeta } from "@/core/helpers/utility";

export default defineComponent({
    name: "FormDatepicker",
    components: {
        VueDatePicker,
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

        const v = computed(() => {
            if (props.modelValue) {
                const dateFormat = $headMeta("moment_date_format");
                return moment(props.modelValue, dateFormat).toDate();
            }
            return null;
        });

        const datePickerFormat = computed(() => {
            return $headMeta("datepicker_date_format") || "dd/MM/yyyy";
        });

        const updateValue = (event) => {
            const dateFormat = $headMeta("moment_date_format");
            const value = moment(event).format(dateFormat);
            emit("update:modelValue", value);
        };

        const isDarkMode = computed(() => {
            return localStorage.getItem("kt_theme_mode_value") === "dark";
        });

        onMounted(() => {
            /*if (props.modelValue) {
                const value = props.modelValue;
                emit("update:modelValue", null);
                setTimeout(() => {
                    emit("update:modelValue", value);
                }, 50);
            }*/
        });

        return {
            datePickerFormat,
            isDarkMode,
            inputValue,
            placeholderText,
            updateValue,
            v,
        };
    },
});
</script>

<style scoped></style>
