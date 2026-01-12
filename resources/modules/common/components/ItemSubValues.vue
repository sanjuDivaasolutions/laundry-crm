<template>
    <span v-for="(f, idx) in itemFields"
        ><span v-if="getFieldValue(item, f, '')">{{
            f.label + ": " + getFieldValue(item, f)
        }}</span
        ><br v-if="idx > 0 && getFieldValue(item, f, '')"
    /></span>
</template>

<script setup>
import { ref } from "vue";

defineProps({
    item: {
        type: Object,
        required: true,
    },
});

const getFieldValue = (item, field, falseValue = "-") => {
    const v = _.get(item, field.value);
    return v ? v : falseValue;
};

const itemFields = ref([
    { value: "blend", label: "Blend" },
    { value: "unit.name", label: "Unit" },
    { value: "shade.name", label: "Shade" },
    { value: "sub_shade.name", label: "Sub Shade" },
    { value: "design_number", label: "Design #" },
    { value: "gsm", label: "GSM" },
    { value: "hsn", label: "HSN" },
]);
</script>

<style scoped></style>
