<template>
    <span :class="badgeClass" v-for="v in values">{{ v }}</span>
</template>

<script>
export default {
    props: ["row", "field"],
    computed: {
        values() {
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
                return rows.map((f) => {
                    return _.get(f, field);
                });
            } else {
                return _.get(this.row, this.field);
            }
        },
        badgeClass() {
            const propBadgeClass = _.get(
                this.xprops,
                `badgeClass.${this.field}`,
                "badge-info"
            );
            let badgeClass = "badge me-1";
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
