import { defineStore } from "pinia";

export const useMagicDatatableStore = defineStore({
    id: "magic-datatable",
    state: () => ({
        tableColumns: [],
    }),
    actions: {
        setTableColumns(columns) {
            this.tableColumns = columns;
        },
    },
});
