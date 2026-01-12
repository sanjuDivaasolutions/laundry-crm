<template>
    <div>
        <MagicDatatable
            :module="module"
            :disable-actions="true"
            table-title="Contract Installments"
            :show-table-title="true"
        />
    </div>
</template>

<script setup>
import { onBeforeMount, onBeforeUnmount, onMounted, ref } from "vue";
import emitter from "@/core/plugins/mitt";
import { useRoute } from "vue-router";
import MagicDatatable from "@/components/magic-datatable/MagicDatatable.vue";

const route = useRoute();

const props = defineProps({
    entry: {
        type: Object,
        required: true,
    },
});

const module = ref({
    id: "contract-installments",
    slug: "installments",
    name: "Installments",
    route: "installments",
    routeOriginal: "installments",
    permission: "installment",
    permission_prefix: `installment_`,
    query: { sort: "date", order: "asc", limit: 200, s: "" },
    formType: "modal",
    formClickAction: `init-installments-form-modal`,
    tableRowClick: {
        enabled: false,
    },
});

const moduleActive = ref(false);

/*const columns = [
    {
        title: `general.fields.date`,
        field: "date",
        thComp: "TranslatedHeader",
        /!*tdComp: "DatatableLink",*!/
        sortable: true,
    },
    {
        title: `general.fields.process`,
        field: "process.name",
        thComp: "TranslatedHeader",
        tdComp: "DatatableDeep",
        sortable: true,
    },
    {
        title: `general.fields.supplier`,
        field: "supplier.display_name",
        thComp: "TranslatedHeader",
        tdComp: "DatatableDeep",
        sortable: true,
    },
    {
        title: `general.fields.user`,
        field: "user.name",
        thComp: "TranslatedHeader",
        tdComp: "DatatableDeep",
        sortable: true,
    },
    /!*{
        title: `general.fields.remark`,
        field: "remark",
        thComp: "TranslatedHeader",
        /!*tdComp: "DatatableLink",*!/
        sortable: true,
    },*!/
    {
        title: "Actions",
        field: "title",
        thComp: "TranslatedHeader",
        tdComp: "DatatableActions",
        isActions: true,
        sortable: false,
    },
];*/

const refreshData = () => {
    emitter.emit("refresh-magic-table-data", {
        id: `magic-table-${module.value.id}`,
    });
};

onBeforeMount(() => {
    const parentId = route.params.id;
    module.value.route = `installments/${parentId}`;
});

onMounted(() => {
    moduleActive.value = true;
});

onBeforeUnmount(() => {
    moduleActive.value = false;
});
</script>

<style scoped>
.table td {
    border-bottom-width: 0;
}
</style>
