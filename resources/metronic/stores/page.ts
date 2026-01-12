import { defineStore } from "pinia";
import { ref } from "vue";

export const usePageStore = defineStore("page", () => {
    const defaultConfig = ref({
        hasActionButton: false,
        actionButtons: [],
    });

    const configs = ref(defaultConfig.value);

    function setConfigs(payload: any) {
        configs.value = { ...defaultConfig.value, ...payload };
    }

    function getConfig(key: string) {
        return configs.value[key];
    }

    function resetConfigs() {
        configs.value = defaultConfig.value;
    }

    return {
        configs,
        setConfigs,
        getConfig,
        resetConfigs,
    };
});
