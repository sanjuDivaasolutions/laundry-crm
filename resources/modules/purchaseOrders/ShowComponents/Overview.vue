<template>
    <div class="card mb-5 mb-xl-10" id="kt_profile_details_view">
        <div class="card-header cursor-pointer">
            <div class="card-title m-0">
                <h3 class="fw-bold m-0">Profile Details</h3>
            </div>

            <router-link v-if="false" to="/account/settings"
                class="btn btn-primary align-self-center">Edit</router-link>
        </div>

        <div class="card-body p-9">
            <div class="row mb-7" v-for="f in fields">
                <label class="col-lg-4 fw-semobold text-muted">{{
                $t(f.label)
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
