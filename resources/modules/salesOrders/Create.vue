<template>
    <ModuleForm :mode="mode"></ModuleForm>
</template>

<script setup>
import ModuleForm from "./SoForm.vue";
import { ref } from "vue";

const mode = ref("create");

</script>