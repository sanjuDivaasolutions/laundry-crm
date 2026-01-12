<template>
    <button
        type="button"
        class="btn btn-link p-0 border-0"
        @click.stop="goToPayments"
        v-html="badgeHtml"
    ></button>
</template>

<script setup>
import { computed } from "vue";
import { useRouter } from "vue-router";

const props = defineProps({
    row: {
        type: Object,
        required: true,
    },
    value: {
        type: [String, null],
        default: null,
    },
});

const router = useRouter();

const badgeHtml = computed(() => {
    return props.row?.payment_status_badge || props.value || "";
});

const goToPayments = () => {
    if (!props.row?.id) {
        return;
    }

    router.push({
        name: "sales-invoices.show",
        params: { id: props.row.id, tab: "payments" },
        query: { makePayment: "1" },
    });
};
</script>
