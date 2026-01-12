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
  -  *  Last modified: 29/01/25, 11:03 am
  -  *  Written by Chintan Bagdawala, 2025.
  -  */
  -->

<template>
    <Icon
        :icon="this.icon"
        :class="noMargin ? '' : 'mr'"
        :width="width"
        :height="height"
    />
</template>

<script>
import { Icon } from "@iconify/vue";

export default {
    name: "FormIcon",
    components: {
        Icon,
    },
    props: {
        icon: {
            type: String,
            default: undefined,
        },
        noMargin: {
            type: Boolean,
            default: false,
        },
        width: {
            type: String,
            default: null,
        },
        height: {
            type: String,
            default: null,
        },
    },
};
</script>

<style scoped>
.mr {
    margin-right: 0.2rem;
}
</style>
