<template>
    <RouterLink :to="route" :target="openInNewTab ? '_blank' : '_self'">{{
        getValue
    }}</RouterLink>
</template>

<script>
export default {
    props: ["row", "value", "xprops"],
    computed: {
        openInNewTab() {
            return _.get(this.xprops, "linkOpenInNewTab", true);
        },
        route() {
            const route = "sales-orders.show";
            const id = _.get(this.row, "sales_order_id", null);
            return { name: route, params: { id: id } };
        },
        getValue() {
            return _.get(this.row, "sales_order.so_number", null);
        },
    },
};
</script>
<style scoped>
a:hover {
    color: var(--link);
}
</style>
