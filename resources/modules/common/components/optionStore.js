import { defineStore } from "pinia";
import ApiService from "@/core/services/ApiService";
import { ref } from "vue";

export const useOptionStore = defineStore("options", () => {
    const options = ref([]);
    let preserveKeys = [];
    let preloadOptionKeys = [];
    let clearTimeout = 300;

    let preloaded = ref(false);

    const hasOption = (key) => {
        return options[key] !== undefined;
    };
    const getOption = (key) => {
        return options[key];
    };

    const setupKeys = async () => {
        const response = await ApiService.get("keys");
        const responseKeys = response.data.keys;
        preloadOptionKeys = response.data.options;
        responseKeys.forEach((key) => {
            preserveKeys.push(key);
        });
        clearTimeout = response.data.timeout;
        /*if (!preloaded.value && authStore.isAuthenticated) {
            await preloadOptions();
        }*/
    };

    const setOptions = (data) => {
        Object.keys(data).forEach((key) => {
            setOption(key, data[key]);
        });
    };

    const setOption = (key, value) => {
        options[key] = value;
    };

    const unsetOption = (key) => {
        delete options[key];
    };

    const clearOptions = () => {
        Object.keys(options).forEach((key) => {
            if (!preserveKeys.includes(key)) {
                delete options[key];
            }
        });
    };

    const preloadOptions = async () => {
        if (preloaded.value) return;
        if (!preloadOptionKeys.length) {
            preloaded.value = true;
            return;
        }
        const params = preloadOptionKeys.join(",");
        const response = await ApiService.get(`bulk-options/${params}`);
        await setOptions(response.data.data);
        preloaded.value = true;
        return true;
    };

    const reloadOption = async (key) => {
        const response = await ApiService.get(`options/${key}`);
        await setOption(key, response.data.data);
        return true;
    };

    return {
        options,
        preloaded,
        setupKeys,
        hasOption,
        getOption,
        setOption,
        unsetOption,
        reloadOption,
        preloadOptions,
        clearOptions,
    };
});
