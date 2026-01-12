<template>
    <RouterLink :to="openLink" :target="openInNewTab ? '_blank' : '_self'">{{
        value
    }}</RouterLink>
</template>

<script>
/* This component is perfectly installed in FIR-wise P&L Report. Check Purchases & Sales link */
export default {
    props: ["row", "value", "xprops", "field"],
    computed: {
        openInNewTab() {
            const linkRoute = _.get(
                this.xprops,
                `linkRoute.${this.field}`,
                null
            );
            return _.get(linkRoute, "openInNewTab", false);
        },
        openLink() {
            const linkRoute = _.get(
                this.xprops,
                `linkRoute.${this.field}`,
                null
            );
            if (!linkRoute) {
                console.error("linkRoute is not defined");
                return {};
            }
            const idField = _.get(linkRoute, "idField", "id");
            const id = _.get(this.row, idField, null);
            const route = _.get(linkRoute, "route", null);
            const params = _.get(linkRoute, "params", {});
            if (!id || !route) {
                console.error("id or route is not defined");
                return {};
            }
            return {
                name: `${route}`,
                params: { id, ...params },
            };
        },
    },
    methods: {},
};
</script>
<style scoped>
a:hover {
    color: var(--link);
}
</style>
