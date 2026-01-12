<template>
    <ModuleForm :mode="mode"></ModuleForm>
</template>

<script setup>
import ModuleForm from "./AgForm.vue";
import { ref } from "vue";

const mode = ref("edit");
</script>
