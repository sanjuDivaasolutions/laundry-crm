<template>
    <div
        class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-800 menu-state-bg-light-primary fw-semobold py-4 w-250px fs-6"
        data-kt-menu="true"
    >
        <div v-for="a in actions" :class="getClass(a)">
            <a
                v-if="a.displayType !== 'separator'"
                @click.prevent="fireEvent(a)"
                href="#"
                class="menu-link px-5 d-flex align-items-center fw-bold"
                ><FormIcon v-if="a.icon" :icon="a.icon" class="me-2" />{{
                    a.label
                }}</a
            >
        </div>
    </div>
</template>

<script setup>
import emitter from "@/core/plugins/mitt";
import router from "@/router";

const props = defineProps({
    actions: {
        type: Array,
        default: () => [],
    },
});

const fireEvent = (action) => {
    if (action.type === "event") {
        let event = action.link;
        if (action.childEvent) {
            event = "bulk-action-event";
        }
        emitter.emit(event, action);
    } else {
        router.push(action.link);
    }
};

const getClass = (action) => {
    return {
        "menu-item px-5": action.displayType !== "separator",
        "separator my-3": action.displayType === "separator",
    };
};
</script>

<style scoped>
.menu-item a:hover {
    color: var(--bs-primary) !important;
}
</style>
