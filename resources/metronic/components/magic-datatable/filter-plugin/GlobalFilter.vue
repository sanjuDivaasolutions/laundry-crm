<!--
  - /*
  -  *  Copyright (c) 2024 Divaa Solutions. All rights reserved.
  -  *
  -  *  This software is the confidential and proprietary information of Divaa Solutions
  -  *  ("Confidential Information"). You shall not disclose such Confidential Information and
  -  *  shall use it only in accordance with the terms of the license agreement you entered into
  -  *  with Divaa Solutions.
  -  *
  -  *  Unauthorized copying of this file, via any medium is strictly prohibited.
  -  *  Proprietary and confidential.
  -  *
  -  *  Last modified: 12/12/24, 9:12 am
  -  *  Written by Chintan Bagdawala, 2024.
  -  */
  -->

<template>
    <div class="row">
        <div class="col d-flex align-items-center">
            <FormFields
                :entry="query"
                :field-label="false"
                :fields="comOutsideFilters"
                :inline="true"
                cancel-class="btn btn-sm btn-icon btn-danger"
                cancel-text=""
                submit-class="btn btn-sm btn-icon btn-primary"
                submit-icon="feather:search"
                submit-text=""
                @cancel="handleClear"
                @submit="handleSubmit"
            />
            <div v-if="comInsideFilters.length">
                <ModalContainer
                    :id="modal.id"
                    size="''"
                    title="Advanced Filters"
                >
                    <template #body>
                        <FormFields
                            :entry="query"
                            :fields="comInsideFilters"
                            cancel-text="Clear"
                            submit-icon="feather:search"
                            submit-text="Apply Filters"
                            @cancel="handleClear"
                            @submit="handleSubmit"
                        />
                    </template>
                </ModalContainer>
            </div>
        </div>
    </div>
</template>

<script>
import ModalContainer from "@common@/modals/ModalContainer.vue";
import FormFields from "@common@/components/FormFields.vue";
import emitter from "@/core/plugins/mitt";

export default {
    name: "GlobalFilter",
    components: { FormFields, ModalContainer },
    props: {
        filters: {
            type: Array,
            required: true,
        },
        query: {
            type: Object,
            required: true,
        },
        defaultQuery: {
            type: Object,
            required: true,
        },
        loading: {
            type: Boolean,
            required: true,
        },
        status: {
            type: String,
            default: "",
        },
        searchFieldOutside: {
            type: Boolean,
            default: true,
        },
        actionButtons: {
            type: Array,
            default: () => [],
        },
        columnChooserLabel: {
            type: String,
            default: "Column Chooser",
        },
        hasColumnChooser: {
            type: Boolean,
            default: true,
        },
    },
    data() {
        return {
            modal: {
                id: "global-filter-modal",
            },
            defaultFilters: [],
            filterData: [],
        };
    },
    computed: {
        comInsideFilters() {
            if (this.searchFieldOutside) {
                return this.defaultFilters.filter(
                    (f) =>
                        f.outside === false || typeof f.outside === "undefined"
                );
            }
            return this.defaultFilters;
        },
        comOutsideFilters() {
            if (this.searchFieldOutside) {
                return this.defaultFilters.filter((f) => f.outside === true);
            }
            return this.defaultFilters;
        },
        searchFieldFilters() {
            if (this.searchFieldOutside) {
                return this.defaultFilters.filter((f) => f.name === "s");
            }
            return [];
        },
    },
    methods: {
        setupValues(obj) {
            //obj = query
            this.defaultFilters.forEach((s) => {
                if (this[obj].hasOwnProperty(s.name)) {
                    s.value = this[obj][s.name];
                }
            });
        },
        handleSubmit() {
            const query = _.cloneDeep(this.query);
            this.defaultFilters.forEach((s) => {
                if (s.value && !query.hasOwnProperty(s.name)) {
                    query[s.name] = s.value;
                }
            });
            this.$emit("filter-submit", query);
            this.closeAdvanceFilterModal();
        },
        handleClear() {
            this.$emit("filter-submit", _.cloneDeep(this.defaultQuery));
            this.defaultFilters = _.cloneDeep(this.filters);
            this.closeAdvanceFilterModal();
        },
        initAdvanceFilterModal() {
            this.advancedFilterModal("show");
        },
        closeAdvanceFilterModal() {
            this.advancedFilterModal("hide");
        },
        advancedFilterModal(event) {
            const payload = {
                id: this.modal.id,
            };
            emitter.emit(`${event}-modal-container`, payload);
        },
    },
    beforeUnmount() {
        this.defaultFilters = [];
        this.entry = {};
        emitter.off("open-advance-filter-modal");
        emitter.emit("kill-events-modal-container");
    },
    mounted() {
        emitter.on("open-advance-filter-modal", this.initAdvanceFilterModal);
        this.defaultFilters = _.cloneDeep(this.filters);
        this.setupValues("query");
    },
};
</script>

<style scoped></style>
