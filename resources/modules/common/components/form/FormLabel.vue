<template>
    <label
        :for="name"
        class="form-label d-flex align-items-center justify-content-between"
    >
        <span
            >{{ $t(label) }}
            <span v-if="required" :class="requiredClass">*</span>
        </span>
        <slot name="link"></slot>
    </label>
</template>

<script>
export default {
    name: "FormLabel",
    props: {
        name: {
            type: String,
            required: true,
        },
        label: {
            type: String,
            default: null,
        },
        required: {
            type: Boolean,
            default: false,
        },
        requiredClass: {
            type: String,
            default: "text-danger",
        },
    },
};
</script>

<style scoped></style>
