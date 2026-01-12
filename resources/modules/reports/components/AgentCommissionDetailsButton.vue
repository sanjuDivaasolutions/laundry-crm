<template>
    <button
        @click="openModal"
        class="btn btn-sm btn-light-primary btn-icon"
        title="View Details"
    >
        <i class="fas fa-eye"></i>
    </button>
</template>

<script setup>
import emitter from "@/core/plugins/mitt";

const props = defineProps({
    row: {
        type: Object,
        required: true
    }
});

const openModal = () => {
    emitter.emit('open-agent-commission-details', props.row);
};
</script>