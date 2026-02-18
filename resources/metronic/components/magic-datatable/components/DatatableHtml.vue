<template>
    <span v-html="sanitizedValue"></span>
</template>

<script>
import { $getDisplayValue } from "@/core/helpers/utility";
import DOMPurify from "dompurify";

export default {
    props: ["xprops", "row", "field"],
    computed: {
        sanitizedValue() {
            const raw = $getDisplayValue(this.row, this.field);
            return DOMPurify.sanitize(raw);
        },
    },
};
</script>