<template>
    <CardContainer
        v-if="initialLoad"
        :hide-footer="true"
        :hide-header="true"
        body-padding="no_padding"
    >
        <div class="p-5">
            <h5 class="mb-2">Developer Options</h5>
            <button
                v-for="action in actions"
                :key="action.code"
                :disabled="isLoading"
                class="btn btn-sm btn-danger me-2"
                @click="performAction(action.code)"
            >
                {{ action.buttonLabel }}
            </button>
        </div>
    </CardContainer>
</template>

<script setup>
import CardContainer from "@common@/components/CardContainer.vue";
import ApiService from "@/core/services/ApiService";
import { $toastSuccess } from "@/core/helpers/utility";

const props = defineProps({
    initialLoad: Boolean,
    isLoading: Boolean,
});

const actions = [
    {
        code: "reinstall-permissions",
        buttonLabel: "Reinstall Permissions",
        confirmMessage: "Are you sure you want to reinstall permissions?",
        successMessage: "Permissions reinstalled successfully",
        apiEndpoint: "reinstall-permissions",
    },
    {
        code: "reset-language",
        buttonLabel: "Reset Language",
        confirmMessage: "Are you sure you want to reset language terms?",
        successMessage: "Language terms reset successfully",
        apiEndpoint: "update-language-terms",
    },
    {
        code: "artisan-optimize",
        buttonLabel: "Artisan Optimize",
        confirmMessage: "Are you sure you want to optimize?",
        successMessage: "Artisan optimized successfully",
        apiEndpoint: "artisan-optimize",
    },
    //storage link
    {
        code: "storage-link",
        buttonLabel: "Storage Link",
        confirmMessage: "Are you sure you want to create storage link?",
        successMessage: "Storage link created successfully",
        apiEndpoint: "storage-link",
    },
];

const performAction = (actionCode) => {
    const action = actions.find((a) => a.code === actionCode);
    if (!action) {
        console.error("Invalid action code");
        return;
    }

    const confirmed = confirm(action.confirmMessage);
    if (!confirmed) {
        return;
    }

    ApiService.get(action.apiEndpoint).then(() => {
        $toastSuccess(action.successMessage);
        window.location.reload();
    });
};
</script>

<style scoped></style>
