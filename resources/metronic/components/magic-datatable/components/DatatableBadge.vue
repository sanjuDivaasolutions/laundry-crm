<template>
    <span :class="badgeClass">{{ value }}</span>
</template>

<script>
import { $getDisplayValue } from "@/core/helpers/utility";

export default {
    props: ["xprops", "row", "field"],
    computed: {
        value() {
            return $getDisplayValue(this.row, this.field);
        },
        badgeClass() {
            const propBadgeClass = _.get(
                this.xprops,
                `badgeClass.${this.field}`,
                "badge-info"
            );
            let badgeClass = "badge";
            badgeClass += " " + propBadgeClass;
            return badgeClass;
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
