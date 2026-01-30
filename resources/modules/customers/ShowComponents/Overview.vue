<template>
    <div class="card mb-5 mb-xl-10">
        <div class="card-header cursor-pointer">
            <div class="card-title m-0">
                <h3 class="fw-bold m-0">Customer Details</h3>
            </div>
        </div>

        <div class="card-body p-9">
            <div class="row mb-7" v-for="f in fields" :key="f.field">
                <label class="col-lg-4 fw-semobold text-muted">{{
                    f.label
                }}</label>
                <div class="col-lg-8">
                    <span class="fw-bold fs-6 text-dark">{{
                        entry[f.field] || "-"
                    }}</span>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>

const props = defineProps({
    entry: {
        type: Object,
        required: true,
    },
    fields: {
        type: Array,
        required: true,
    },
});
</script>
