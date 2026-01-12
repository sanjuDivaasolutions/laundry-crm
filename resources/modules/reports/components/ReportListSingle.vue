<template>
    <div class="card card-xl-stretch mb-xl-8 h-100">
        <div class="card-header border-0 pt-5">
            <h3 class="card-title align-items-start flex-column">
                <span
                    class="card-label fw-bold text-dark d-flex align-items-start"
                    ><FormIcon icon="feather:shopping-cart" class="me-2" />{{
                        title
                    }}</span
                >
            </h3>
        </div>
        <div class="card-body pt-0">
            <div
                v-for="s in list"
                class="d-flex align-items-center justify-content-between border border-dashed border-gray-300 rounded px-7 py-3 mb-2"
            >
                <RouterLink
                    :to="s.to"
                    class="fs-5 text-dark text-hover-primary fw-semobold w-100"
                    >{{ s.title }}</RouterLink
                >
            </div>
        </div>
    </div>
</template>

<script setup>
const props = defineProps({
    title: {
        type: String,
        required: true,
    },
    list: {
        type: Array,
        required: true,
    },
});
</script>

<style scoped></style>
