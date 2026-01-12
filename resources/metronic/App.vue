<template>
    <RouterView />
</template>

<script lang="ts">
import { defineComponent, nextTick, onBeforeMount, onMounted } from "vue";
import { RouterView } from "vue-router";
import { useConfigStore } from "@/stores/config";
import { useThemeStore } from "@/stores/theme";
import { useBodyStore } from "@/stores/body";
import { themeMode } from "@/core/helpers/config";
import { initializeComponents } from "@/core/plugins/keenthemes";

export default defineComponent({
    name: "app",
    components: {
        RouterView,
    },
    setup() {
        const configStore = useConfigStore();
        const themeStore = useThemeStore();
        const bodyStore = useBodyStore();

        onBeforeMount(() => {
            /**
             * Overrides the layout config using saved data from localStorage
             * remove this to use static config (@/core/config/DefaultLayoutConfig.ts)
             */
            configStore.overrideLayoutConfig();

            /**
             *  Sets a mode from configuration
             */
            themeStore.setThemeMode(themeMode.value);
        });

        onMounted(() => {
            nextTick(() => {
                initializeComponents();

                bodyStore.removeBodyClassName("page-loading");
            });
        });
    },
});
</script>

<style lang="scss">
#app {
    display: contents;
}
</style>
