<template>
    <li class="nav-item">
        <router-link
            :to="{
                name: route,
                params: { id: id, tab: slug },
            }"
            @click.prevent="switchTab(slug)"
            class="nav-link text-active-primary me-6"
            :class="slug === selectedSlug ? 'active' : ''"
            >{{ label }}</router-link
        >
    </li>
</template>

<script setup>
import { computed } from "vue";

const props = defineProps({
    id: {
        type: String,
        required: true,
    },
    route: {
        type: String,
        required: true,
    },
    entry: {
        type: Object,
        required: true,
    },
    selectedSlug: {
        type: String,
        required: true,
    },
});

const emit = defineEmits(["switchTab"]);

const slug = computed(() => {
    return props.entry.slug;
});

const label = computed(() => {
    return props.entry.label || "-";
});

const switchTab = (tabName) => {
    emit("switchTab", tabName);
};
</script>

<style scoped></style>
