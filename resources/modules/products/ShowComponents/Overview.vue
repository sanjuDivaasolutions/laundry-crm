<template>
    <div class="card mb-5 mb-xl-10" id="kt_profile_details_view">
        <div class="card-header cursor-pointer">
            <div class="card-title m-0">
                <h3 class="fw-bold m-0">Item Details</h3>
            </div>

            <router-link
                v-if="false"
                to="/account/settings"
                class="btn btn-primary align-self-center"
                >Edit</router-link
            >
        </div>

         <div class="card-body p-9">
            <ShowValues :entry="entry" :fields="fields" />
        </div>
    </div>
</template>

<script setup>
import ShowValues from "@common@/components/show/ShowValues.vue";
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
