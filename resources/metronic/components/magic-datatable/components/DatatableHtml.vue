<template>
    <span v-html="value"></span>
</template>

<script>
import { $getDisplayValue } from "@/core/helpers/utility";

export default {
    props: ["xprops", "row", "field"],
    computed: {
        value() {
            return $getDisplayValue(this.row, this.field);
        },
    },
};
</script>