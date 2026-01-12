<template>
    <div class="table-responsive">
        <table class="table table-sm table-row-gray-400 gy-0 gs-0 mb-0">
            <thead>
                <tr>
                    <th class="p-0"></th>
                    <th class="text-primary p-0">
                        <small><strong>Dr</strong></small>
                    </th>
                    <th class="text-primary p-0">
                        <small><strong>Cr</strong></small>
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="i in items">
                    <td class="px-0 py-1">
                        <small>{{ i.account_label }}</small>
                    </td>
                    <td class="px-0 py-1 fw-bold">
                        <small>{{ i.debit > 0 ? i.debit_label : "-" }}</small>
                    </td>
                    <td class="px-0 py-1 fw-bold">
                        <small>{{ i.credit > 0 ? i.credit_label : "-" }}</small>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<script>
export default {
    props: ["field", "row"],
    computed: {
        items() {
            return _.get(this.row, this.field);
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
