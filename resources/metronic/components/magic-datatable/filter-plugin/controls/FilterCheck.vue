<template>
    <VField class="pt-0 pb-0">
        <FormSwitchBlock
            :label="c.label"
            :value="c.value"
            :thin="true"
            @input="onChange"
        />
    </VField>
</template>

<script>
export default {
    name: "FilterCheck",
    props: ["c"],
    methods: {
        onChange(e) {
            this.c.value = e;
            if (
                typeof this.c.changeOnCheck !== "undefined" &&
                this.c.changeOnCheck === true
            ) {
                this.$emit("entered");
            }
            return true;
        },
    },
};
</script>

<style scoped></style>
