<template>
    <div>
        <FormLabel
            v-if="label"
            :name="name"
            :label="label"
            :required="required"
        />
        <VueDatePicker
            :range="true"
            :name="name"
            input-class-name="form-control form-control-solid"
            :placeholder="placeholderText"
            :autocomplete="autocomplete"
            :model-value="v"
            @update:model-value="updateValue"
            :auto-apply="true"
            :required="required"
            :readonly="readonly"
            control-container-class="form-control form-control-solid"
            :clearable="false"
            :enable-time-picker="false"
            :dark="isDarkMode"
            :append-to-body="true"
            :format="datePickerFormat"
            :preset-dates="presetDates"
        />
    </div>
</template>

<script>
import { computed, defineComponent, ref } from "vue";
import moment from "moment";
import VueDatePicker from "@vuepic/vue-datepicker";
import { $headMeta } from "@/core/helpers/utility";

export default defineComponent({
    name: "FormDateRangePicker",
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
            type: [String],
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
        const placeholderText = computed(() => {
            return props.placeholder ? props.placeholder : props.label;
        });

        const presetDates = ref([
            { label: "Today", value: [new Date(), new Date()] },
            {
                label: "Yesterday",
                value: [
                    moment().subtract(1, "days").toDate(),
                    moment().subtract(1, "days").toDate(),
                ],
            },
            {
                label: "Last 7 days",
                value: [moment().subtract(6, "days").toDate(), new Date()],
            },
            {
                label: "Last 30 days",
                value: [moment().subtract(29, "days").toDate(), new Date()],
            },
            {
                label: "This Week",
                value: [moment().startOf("week").toDate(), new Date()],
            },
            {
                label: "Last Week",
                value: [
                    moment().subtract(1, "week").startOf("week").toDate(),
                    moment().subtract(1, "week").endOf("week").toDate(),
                ],
            },
            {
                label: "This month",
                value: [moment().startOf("month").toDate(), new Date()],
            },
            {
                label: "Last month",
                value: [
                    moment().subtract(1, "month").startOf("month").toDate(),
                    moment().subtract(1, "month").endOf("month").toDate(),
                ],
            },
            {
                label: "This year",
                value: [
                    moment().startOf("year").toDate(),
                    moment().endOf("year").toDate(),
                ],
            },
            {
                label: "Last year",
                value: [
                    moment().subtract(1, "year").startOf("year").toDate(),
                    moment().subtract(1, "year").endOf("year").toDate(),
                ],
            },
            {
                label: "Last 12 months",
                value: [
                    moment().subtract(12, "months").toDate(),
                    moment().toDate(),
                ],
            },
        ]);

        //const v = ref({ startDate: null, endDate: null });
        const v = computed(() => {
            if (!props.modelValue) return null;
            const dateFormat = $headMeta("moment_date_format");
            const dateArr = props.modelValue.split(" to ");
            const dateFrom = dateArr[0]
                ? moment(dateArr[0], dateFormat).toDate()
                : null;
            const dateTo = dateArr[1]
                ? moment(dateArr[1], dateFormat).toDate()
                : null;
            return [dateFrom, dateTo];
        });

        const datePickerFormat = computed(() => {
            return $headMeta("datepicker_date_format") || "dd/MM/yyyy";
        });

        const isDarkMode = computed(() => {
            return localStorage.getItem("kt_theme_mode_value") === "dark";
        });

        const updateValue = (event) => {
            const dateFormat = $headMeta("moment_date_format");
            const modelValue = event
                ? `${moment(event[0]).format(dateFormat)} to ${moment(
                      event[1]
                  ).format(dateFormat)}`
                : "";
            emit("update:model-value", modelValue);
        };

        /*const setValue = () => {
            if (props.modelValue) {
                const dateArr = props.modelValue.split(" to ");
                if (!dateArr[1]) return;
                v.value = {
                    startDate: moment(dateArr[0], "MM/DD/YYYY").toDate(),
                    endDate: moment(dateArr[1], "MM/DD/YYYY").toDate(),
                };
            }
        };

        onMounted(() => {
            //setValue();
        });*/

        return {
            presetDates,
            isDarkMode,
            datePickerFormat,
            placeholderText,
            updateValue,
            v,
        };
    },
});
</script>

<style scoped></style>
