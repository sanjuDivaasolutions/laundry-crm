<template>
    <span>
        <span>{{ value ? "Yes" : "No" }}</span>
    </span>
</template>

<script>
export default {
    props: ["value"],
};
</script>

<style scoped>
.check {
    opacity: 1 !important;
}

.form-check .form-check-label {
    cursor: default !important;
}
</style>
