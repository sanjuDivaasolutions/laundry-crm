<template>
    <VDatePicker v-model="range" is-range color="green" trim-weeks>
        <template #default="{ inputValue, inputEvents }">
            <VField addons>
                <VControl>
                    <input
                        :value="inputValue.start"
                        class="input"
                        v-on="inputEvents.start"
                    />
                </VControl>
                <VControl>
                    <a class="button is-static"
                        ><i class="iconify" data-icon="feather:arrow-right"></i
                    ></a>
                </VControl>
                <VControl>
                    <input
                        :value="inputValue.end"
                        class="input"
                        v-on="inputEvents.end"
                    />
                </VControl>
            </VField>
        </template>
    </VDatePicker>
</template>

<script>
export default {
    name: "FilterDateRange",
    props: ["c"],
};
</script>

<style scoped></style>
