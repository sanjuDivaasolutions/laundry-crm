<!--
  - /*
  -  *  Copyright (c) 2025 Divaa Solutions. All rights reserved.
  -  *
  -  *  This software is the confidential and proprietary information of Divaa Solutions
  -  *  ("Confidential Information"). You shall not disclose such Confidential Information and
  -  *  shall use it only in accordance with the terms of the license agreement you entered into
  -  *  with Divaa Solutions.
  -  *
  -  *  Unauthorized copying of this file, via any medium is strictly prohibited.
  -  *  Proprietary and confidential.
  -  *
  -  *  Last modified: 29/01/25, 6:31 pm
  -  *  Written by Chintan Bagdawala, 2025.
  -  */
  -->

<template>
    <div class="d-flex flex-column flex-lg-row flex-column-fluid">
        <div
            class="d-flex flex-column flex-lg-row-fluid w-lg-50 p-10 order-2 order-lg-1"
        >
            <div class="d-flex flex-center flex-column flex-lg-row-fluid">
                <div class="d-flex flex-center flex-column flex-lg-row-fluid p-10">
                    <router-view></router-view>
                </div>
            </div>
        </div>

        <div
            class="d-flex flex-lg-row-fluid w-lg-50 bgi-size-cover bgi-position-center order-1 order-lg-2"
            style="background-image: url('/images/bg/auth-bg.jpg')"
        >
            <div
                class="d-flex flex-column flex-center py-7 py-lg-15 px-5 px-md-15 w-100"
            >
                <router-link to="/" class="mb-0 mb-lg-12">
                    <img
                        alt="Logo"
                        :src="$url(`/images/logo/custom-1.svg`)"
                        class="h-160px h-lg-350px"
                    />
                </router-link>
            </div>
        </div>
    </div>
</template>

<script lang="ts">
import { defineComponent, onMounted } from "vue";
import LayoutService from "@/core/services/LayoutService";
import { useBodyStore } from "@/stores/body";

export default defineComponent({
    name: "auth-layout",
    components: {},
    setup() {
        const store = useBodyStore();

        onMounted(() => {
            LayoutService.emptyElementClassesAndAttributes(document.body);

            store.addBodyClassname("app-blank");
            store.addBodyClassname("bg-body");
        });
    },
});
</script>
