<template>
    <div class="card mb-5 mb-xl-10">
        <div class="card-body pt-9 pb-0">
            <div class="d-flex flex-wrap flex-sm-nowrap">
                <div class="flex-grow-1">
                    <div
                        class="d-flex justify-content-between align-items-start flex-wrap"
                    >
                        <div class="d-flex flex-column">
                            <div class="d-flex align-items-center">
                                <a
                                    href="javascript:void(0);"
                                    class="text-gray-800 text-hover-primary fs-2 fw-bold me-1"
                                    >{{ title }}</a
                                >
                            </div>
                            <div class="d-flex flex-wrap fw-semobold fs-6 pe-2">
                                <a
                                    href="#"
                                    class="d-flex align-items-center text-gray-400 text-hover-primary me-5"
                                >
                                    <span class="svg-icon svg-icon-4 me-1">
                                        <inline-svg
                                            src="/media/icons/duotune/communication/com006.svg"
                                        />
                                    </span>
                                    {{ subTitle }}
                                </a>
                            </div>
                        </div>
                        <div class="d-flex my-4">
                            <RouterLink
                                :title="routeTitle"
                                :to="route"
                                class="btn btn-sm btn-primary me-3"
                                ><FormIcon icon="feather:arrow-left"
                            /></RouterLink>
                        </div>
                    </div>
                </div>
            </div>
            <slot name="tabs"></slot>
        </div>
    </div>
</template>

<script setup>
const props = defineProps({
    title: {
        type: String,
        default: "Title",
    },
    subTitle: {
        type: String,
        default: "Sub Title",
    },
    route: {
        type: Object,
        required: true,
    },
    routeTitle: {
        type: String,
        default: "Back to List",
    },
});
</script>

<style scoped></style>
