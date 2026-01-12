<template>
    <span :class="getColor">{{ getLabel }}</span>
</template>

<script>
export default {
    props: ["value"],
    computed: {
        getColor() {
            return this.value === "Yes"
                ? "badge badge-success"
                : "badge badge-danger";
        },
        getLabel() {
            return this.value;
        },
    },
};
</script>

<style scoped></style>
