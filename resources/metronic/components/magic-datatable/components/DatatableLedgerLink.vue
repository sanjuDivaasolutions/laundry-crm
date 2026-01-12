<template>
    <RouterLink :to="route" :target="openInNewTab ? '_blank' : '_self'">{{
        value
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
            const route = _.get(this.row, "reference_route", null);
            const id = _.get(this.row, "reference", null);
            return { name: route, params: { id: id } };
        },
    },
    methods: {
        openLink() {
            const route = _.get(this.row, "reference_route", null);
            const id = _.get(this.row, "reference", null);
            if (!route || !id) {
                console.error("route or id is not defined");
                return false;
            }
            this.$router.push({ name: route, params: { id: id } });
        },
    },
};
</script>
<style scoped>
a:hover {
    color: var(--link);
}
</style>
