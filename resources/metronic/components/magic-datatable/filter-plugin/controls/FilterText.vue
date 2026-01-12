<template>
    <FormInput
        type="text"
        :label="c.label"
        :id="c.name"
        :name="c.name"
        v-model="c.value"
        :placeholder="c.placeholder ? c.placeholder : 'Search...'"
        @keyup.enter="$emit('entered')"
    />
</template>

<script>
export default {
    name: "FilterText",
    props: ["c"],
};
</script>

<style scoped></style>
