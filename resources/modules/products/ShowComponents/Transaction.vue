<!--
  - /*
  -  *  Copyright (c) 2025 Divaa Solutions. All rights reserved.
  -  *
  -  *  This software is the confidential and proprietary information of Divaa Solutions
  -  *  ("Confidential Information"). You shall not disclose such Confidential Information and
  -  *  shall use it only in accordance with the terms of the license agreement you entered into
  -  *  with Divaa Solutions.
  -  *
  -  *  Unauthorized copying of this file, via any medium is strictly prohibited.
  -  *  Proprietary and confidential.
  -  *
  -  *  Last modified: 06/01/25, 6:36 pm
  -  *  Written by Chintan Bagdawala, 2025.
  -  */
  -->

<template>
    <div>
        <CardContainer :hide-header="true" :hide-footer="true" class="mb-10">
        </CardContainer>
        <magic-datatable
            v-if="true"
            :module="module"
            :columns="columns"
            table-title="Transactions"
        />
    </div>
</template>

<script setup>
import { onBeforeMount, onBeforeUnmount, onMounted, ref } from "vue";
import MagicDatatable from "@/components/magic-datatable/MagicDatatable.vue";
import CardContainer from "@common@/components/CardContainer.vue";
import { useRoute } from "vue-router";

const route = useRoute();

const props = defineProps({
    id: {
        type: Number,
        required: true,
    },
    entry: {
        type: Object,
        required: true,
    },
});

const module = ref({
    id: "product-transactions",
    slug: "product-transactions",
    name: "Payment",
    route: "product-transactions",
    permission: "payment",
    permission_prefix: `product_`,
    query: { sort: "date", order: "desc", limit: 100, s: "" },
    tableRowClick: {
        enabled: false,
    },
});

const data = ref([]);

const formModalData = ref({
    id: "payment-form-modal",
    size: "modal-lg",
    route: null,
});
const formMode = ref("create");
const formModalTitle = ref("New Payment");

const columns = [
    {
        title: `general.fields.order_no`,
        field: "order_number",
        thComp: "TranslatedHeader",
        sortable: false,
    },
    {
        title: `general.fields.date`,
        field: "date",
        thComp: "TranslatedHeader",
        sortable: false,
    },
    {
        title: `general.fields.reason`,
        field: "reason_label",
        thComp: "TranslatedHeader",
        sortable: false,
    },
    {
        title: `general.fields.quantity`,
        field: "quantity",
        thComp: "TranslatedHeader",
        sortable: false,
    },
    {
        title: `general.fields.rate`,
        field: "rate",
        thComp: "TranslatedHeader",
        sortable: false,
    },
    {
        title: `general.fields.amount`,
        field: "amount",
        thComp: "TranslatedHeader",
        sortable: false,
    },
];

onBeforeMount(() => {
    module.value.route = `product-transactions/${route.params.id}`;

    formModalData.value.route = `product-transactions`;
});

onMounted(() => {});

onBeforeUnmount(() => {});
</script>

<style scoped>
.table th {
    font-weight: bold;
}
</style>
