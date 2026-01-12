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
  -  *  Last modified: 18/11/24, 4:41 pm
  -  *  Written by Chintan Bagdawala, 2024.
  -  */
  -->

<template>
    <ModuleForm :mode="mode"></ModuleForm>
</template>

<script setup>
import ModuleForm from "./ProductForm.vue";
import { ref } from "vue";

const mode = ref("edit");
</script>
