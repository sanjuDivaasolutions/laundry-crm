<template>
    <a href="javascript:void(0);" @click.prevent="openLink">{{ value }}</a>
</template>

<script>
export default {
    props: ["row", "value", "xprops"],
    methods: {
        openLink() {
            if (!this.row.id || !this.xprops.route) {
                return false;
            }
            const idField = this.xprops.idField || "id";
            this.$router.push({
                name: `${this.xprops.route}.show`,
                params: { id: this.row[idField] },
            });
        },
    },
};
</script>
<style scoped>
a:hover {
    color: var(--link);
}
</style>
