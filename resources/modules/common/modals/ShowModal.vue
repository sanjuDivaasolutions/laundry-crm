<template>
    <ModalContainer :id="id" :title="title">
        <template #body>
            <div class="row">
                <div class="col-12 mb-10">
                    <div class="table-responsive">
                        <table
                            class="table align-middle table-row-bordered table-row-solid mb-0 gy-2"
                        >
                            <tr v-for="f in textFields" :key="f.field">
                                <th class="fw-bold">{{ $t(f.label) }}</th>
                                <td>{{ $getDisplayValue(entry, f.field) }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
                <div
                    v-if="itemFields.length > 0"
                    v-for="f in itemFields"
                    class="col-12"
                >
                    <h3>{{ $t(f.label) }}</h3>
                    <div :class="getTableWrapperClass(f)">
                        <table
                            class="table align-middle table-row-bordered table-row-solid mb-0 gy-2"
                        >
                            <thead>
                                <tr>
                                    <th
                                        class="fw-bold"
                                        :class="getFieldClass(f)"
                                        v-for="f in f.subFields"
                                    >
                                        {{ $t(f.label) }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="itemFieldsLength(f) === 0">
                                    <td
                                        class="text-center"
                                        :colspan="f.subFields.length"
                                    >
                                        No Item
                                    </td>
                                </tr>
                                <tr
                                    v-if="itemFieldsLength(f) > 0"
                                    v-for="i in entry[f.field]"
                                >
                                    <td
                                        v-for="f in f.subFields"
                                        :class="getFieldClass(f)"
                                    >
                                        {{ $getDisplayValue(i, f.field) }}
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr v-if="itemFieldsLength(f) > 0">
                                    <th
                                        class="fw-bold"
                                        :class="getFieldClass(f)"
                                        v-if="f.field"
                                        v-for="f in f.summaryFields"
                                    >
                                        {{ entry[f.field] || "" }}
                                    </th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </template>
    </ModalContainer>
</template>

<script setup>
import ModalContainer from "@common@/modals/ModalContainer.vue";
import emitter from "@/core/plugins/mitt";
import { onMounted, ref, computed } from "vue";
import { $getDisplayValue } from "@/core/helpers/utility";

const props = defineProps({
    id: {
        type: String,
        required: true,
    },
    title: {
        type: String,
        default: "",
    },
    fields: {
        type: Array,
        required: true,
        default: [],
    },
});

const textFields = computed(() => {
    return props.fields.filter(
        (f) => f.type === "text" || typeof f.type === "undefined"
    );
});

const itemFields = computed(() => {
    return props.fields.filter((f) => f.type === "items");
});

const itemFieldsLength = (f) => {
    return entry.value[f.field] ? entry.value[f.field].length : 0;
};

const getFieldClass = (f) => {
    let c = "";
    switch (f.align) {
        case "left":
            c = "text-start";
            break;
        case "center":
            c = "text-center";
            break;
        case "right":
            c = "text-end";
            break;
        default:
            c = "text-start";
    }
    return c;
};

const entry = ref({});
onMounted(() => {
    emitter.on("show-show-modal", (payload) => {
        entry.value = payload.entry;
        emitter.emit("show-modal-container", payload);
    });
});

const getTableWrapperClass = (field) => {
    const classes = ["table-responsive"];
    if (field.scrollable) {
        classes.push("modal-scrollable-table");
    }
    return classes.join(" ");
};
</script>

<style scoped>
.modal-scrollable-table {
    max-height: 400px;
    overflow-y: auto;
}
</style>
