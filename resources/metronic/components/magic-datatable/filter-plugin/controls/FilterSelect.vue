<template>
    <FormSelect
        :label="c.label"
        :name="c.name"
        :value="selectedValues"
        @change="handleChange"
        :options="options"
        id-value="value"
        label-value="text"
        :mode="isMultiple ? 'multiple' : 'single'"
        :close-on-select="!isMultiple"
    />
</template>

<script>
import { EventBus } from "../../../EventBus";

export default {
    name: "FilterSelect",
    props: ["c", "options"],
    data() {
        return {
            selectedValues: [],
        };
    },
    computed: {
        isMultiple() {
            return typeof this.c.multiple === "undefined"
                ? true
                : this.c.multiple;
        },
        isAutocomplete() {
            return typeof this.c.autocomplete === "undefined"
                ? "true"
                : this.c.autocomplete;
        },
    },
    methods: {
        handleChange(value) {
            this.selectedValues = value;
            this.c.value = this.$pluck(value, "value");
        },
    },
    created() {
        EventBus.$on("clear-global-filter", () => {
            this.selectedValues = [];
        });
    },
};
</script>

<style scoped></style>
