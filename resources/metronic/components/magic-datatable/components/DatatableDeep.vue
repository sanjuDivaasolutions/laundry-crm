<template>
    <div>
        <span>
            {{ value }}
        </span>
    </div>
</template>

<script>
import { $getDisplayValue } from "@/core/helpers/utility";

export default {
    props: ["field", "row"],
    computed: {
        value() {
            return $getDisplayValue(this.row, this.field);
        },
    },
};
</script>

<style scoped>
.badge {
    font-size: 0.875rem;
    font-weight: 500;
    text-transform: none;
}
</style>
