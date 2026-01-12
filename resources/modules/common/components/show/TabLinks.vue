<template>
    <div class="d-flex overflow-auto h-55px">
        <ul
            class="nav nav-stretch nav-line-tabs nav-line-tabs-2x border-transparent fs-5 fw-bold flex-nowrap"
        >
            <TabLink
                v-for="entry in entries"
                :id="id"
                :key="entry.slug"
                :entry="entry"
                :slug="entry.slug"
                :selected-slug="selectedSlug"
                @switchTab="switchTab"
            />
        </ul>
    </div>
</template>

<script setup>
import TabLink from "@common@/components/show/TabLink.vue";

const props = defineProps({
    id: {
        type: String,
        required: true,
    },
    route: {
        type: String,
        required: true,
    },
    entries: {
        type: Array,
        required: true,
    },
    selectedSlug: {
        type: String,
        required: true,
    },
});

const emit = defineEmits(["switchTab"]);

const switchTab = (tabName) => {
    emit("switchTab", tabName);
};
</script>

<style scoped></style>
