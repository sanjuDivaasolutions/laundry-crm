<template>
    <FormIcon :icon="getIcon" class="fs-2" :class="getClass" />
</template>

<script>
import FormIcon from "@common@/components/form/FormIcon.vue";

export default {
    components: { FormIcon },
    props: ["value"],
    computed: {
        getClass() {
            switch (this.value) {
                case 1:
                    return "text-success";
                case 0:
                    return "text-danger";
                default:
                    return "text-gray-500";
            }
        },
        getIcon() {
            switch (this.value) {
                case 1:
                    return "feather:check";
                case 0:
                    return "feather:x";
                default:
                    return "feather:minus";
            }
        },
    },
};
</script>

<style scoped></style>
