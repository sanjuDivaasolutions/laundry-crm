<template>
    <span>{{ value }}</span>
</template>

<script>
import { $getDisplayValue } from "@/core/helpers/utility";

export default {
    props: ["row", "field"],
    computed: {
        value() {
            if (this.field.indexOf("*") > -1) {
                const fields = this.field.replaceAll(".", "").split("*");
                if (fields.length !== 2) {
                    return "";
                }
                const rows = _.get(this.row, fields[0]);
                const field = fields[1];
                if (!rows) {
                    return "";
                }
                return rows
                    .map((f) => {
                        return _.get(f, field);
                    })
                    .join(", ");
            } else {
                return $getDisplayValue(this.row, this.field);
            }
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
