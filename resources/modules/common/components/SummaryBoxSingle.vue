<!--
  - /*
  -  *  Copyright (c) 2024 Divaa Solutions. All rights reserved.
  -  *
  -  *  This software is the confidential and proprietary information of Divaa Solutions
  -  *  ("Confidential Information"). You shall not disclose such Confidential Information and
  -  *  shall use it only in accordance with the terms of the license agreement you entered into
  -  *  with Divaa Solutions.
  -  *
  -  *  Unauthorized copying of this file, via any medium is strictly prohibited.
  -  *  Proprietary and confidential.
  -  *
  -  *  Last modified: 12/12/24, 11:26 am
  -  *  Written by Chintan Bagdawala, 2024.
  -  */
  -->

<template>
    <div class="card h-100">
        <div class="overlay-bg" v-if="isLoading"></div>
        <div
            v-if="isLoading"
            class="loading-message position-absolute d-flex h-100 w-100 justify-content-center align-items-center"
        >
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Please wait...</span>
            </div>
        </div>
        <div class="card-body d-flex flex-column px-9 py-5">
            <div class="card-title m-0">
                <a
                    href="javascript:void(0);"
                    class="fs-6 fw-semibold text-gray-600 m-0"
                    >{{ label }}</a
                >
            </div>
            <div class="fs-2 fw-bold" :class="classes">{{ value }}</div>
        </div>
    </div>
</template>

<script setup>
import { computed } from "vue";

const props = defineProps({
    label: {
        type: String,
        default: null,
    },
    value: {
        type: String,
        default: null,
    },
    isLoading: {
        type: Boolean,
        default: false,
    },
    variant: {
        type: String,
        default: "info",
    },
});

const classes = computed(() => {
    return {
        "text-primary": props.variant === "primary",
        "text-success": props.variant === "success",
        "text-info": props.variant === "info",
        "text-danger": props.variant === "danger",
        "text-warning": props.variant === "warning",
        "text-dark": props.variant === "dark",
    };
});
</script>

<style scoped lang="scss">
.overlay-bg {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: #eee;
    opacity: 0.3;
    z-index: 9998;
}
.loading-message {
    z-index: 9999;
}
</style>
