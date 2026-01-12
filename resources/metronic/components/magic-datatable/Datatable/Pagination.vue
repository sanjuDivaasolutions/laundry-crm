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
  -  *  Last modified: 12/12/24, 10:53 am
  -  *  Written by Chintan Bagdawala, 2024.
  -  */
  -->

<template>
    <div class="row">
        <div
            class="col-sm-12 col-md-5 d-flex align-items-center justify-content-center justify-content-md-start"
        >
            <label for="items-per-page">
                <select
                    class="form-select form-select-sm form-select-solid"
                    name="items-per-page"
                    id="items-per-page"
                    :value="limit != null ? String(limit) : ''"
                    @change="changeSizeOption"
                >
                    <option
                        v-for="i in sizeOptions"
                        :key="i"
                        :value="String(i)"
                    >
                        {{ i }}
                    </option>
                </select>
            </label>
            <span class="ms-2" v-if="totalPage > 1 && !loading"
                >Page
                <strong>{{ currentPage }}</strong>
                of
                <strong>{{ totalPage }}</strong>
            </span>
            <span v-if="!isNaN(total) && !loading" class="ms-2"
                >Total: <strong>{{ total }}</strong></span
            >
        </div>
        <div
            class="col-sm-12 col-md-7 d-flex align-items-center justify-content-center justify-content-md-end"
        >
            <div
                v-if="totalPage > 1"
                class="dataTables_paginate paging_simple_numbers"
            >
                <ul class="pagination">
                    <li
                        class="paginate_button page-item"
                        :class="{ disabled: isFirstPage }"
                        :style="{ cursor: !isFirstPage ? 'pointer' : 'auto' }"
                    >
                        <a
                            title="Go to first page"
                            class="page-link"
                            @click="toFirstPage"
                        >
                            <FormIcon icon="feather:chevrons-left" />
                        </a>
                    </li>
                    <li
                        class="paginate_button page-item"
                        :class="{ disabled: isFirstPage }"
                        :style="{ cursor: !isFirstPage ? 'pointer' : 'auto' }"
                    >
                        <a
                            title="Go to previous page"
                            class="page-link"
                            @click="toPrevPage"
                        >
                            <FormIcon icon="feather:chevron-left" />
                        </a>
                    </li>
                    <li
                        v-for="(l, i) in links"
                        class="paginate_button page-item"
                        :class="{
                            active: l.active,
                        }"
                        :style="{
                            cursor: !l.active ? 'pointer' : 'auto',
                        }"
                        :key="i"
                    >
                        <a class="page-link" @click="goToPage(l.label)">
                            {{ l.label }}
                        </a>
                    </li>
                    <li
                        class="paginate_button page-item"
                        :class="{ disabled: isLastPage }"
                        :style="{ cursor: !isLastPage ? 'pointer' : 'auto' }"
                    >
                        <a
                            title="Go to next page"
                            class="paginate_button page-link"
                            @click="toNextPage"
                        >
                            <FormIcon icon="feather:chevron-right" />
                        </a>
                    </li>
                    <li
                        class="paginate_button page-item"
                        :class="{ disabled: isLastPage }"
                        :style="{ cursor: !isLastPage ? 'pointer' : 'auto' }"
                    >
                        <a
                            title="Go to last page"
                            class="paginate_button page-link"
                            @click="toLastPage"
                        >
                            <FormIcon icon="feather:chevrons-right" />
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from "vue";
import { pageSizeOptions } from "@modules@/common/data/datatable";

const props = defineProps({
    query: {
        type: Object,
        required: true,
    },
    meta: {
        type: Object,
        default: () => {},
    },
    loading: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(["update"]);

const sizeOptions = computed(() => {
    return pageSizeOptions;
});

const total = computed(() => {
    return _.get(props.meta, "total", 0);
});

const limit = computed(() => {
    return _.get(props.query, "limit", 10);
});

const isFirstPage = computed(() => _.get(props.meta, "current_page", 1) === 1);
const isLastPage = computed(
    () =>
        _.get(props.meta, "current_page", 1) ===
        _.get(props.meta, "last_page", 1)
);
const totalPage = computed(() => Math.ceil(total.value / +limit.value));
const currentPage = computed(() => _.get(props.meta, "current_page", 1));

const links = computed(() => {
    const links = _.get(props.meta, "links", []);
    if (links.length > 0) {
        return links.filter((l, i) => i !== 0 && i !== links.length - 1);
    }
    return [];
});

const goToPage = (page) => {
    if (page === currentPage.value) {
        return;
    }
    const query = _.cloneDeep(props.query);
    query.offset = (page - 1) * limit.value;
    query.page = page;
    emit("update", query);
};

const toFirstPage = () => {
    const query = _.cloneDeep(props.query);
    query.offset = 0;
    query.page = 1;
    emit("update", query);
};

const toLastPage = () => {
    const query = _.cloneDeep(props.query);
    query.offset = (totalPage.value - 1) * limit;
    query.page = totalPage.value;
    emit("update", query);
};

const toNextPage = () => {
    if (isLastPage.value) {
        return;
    }
    const query = _.cloneDeep(props.query);
    query.offset = currentPage.value * limit.value;
    query.page = currentPage.value + 1;
    emit("update", query);
};

const toPrevPage = () => {
    if (isFirstPage.value) {
        return;
    }
    const query = _.cloneDeep(props.query);
    query.offset = (currentPage.value - 2) * limit.value;
    query.page = currentPage.value - 1;
    emit("update", query);
};

const changeSizeOption = (e) => {
    const query = _.cloneDeep(props.query);
    const selectedLimit = Number(e.target.value);
    query.limit = Number.isNaN(selectedLimit) ? query.limit : selectedLimit;
    query.offset = 0;
    query.page = 1;
    emit("update", query);
};
</script>

<style scoped>
.form-select.form-select-solid {
    border-color: #ccc !important;
    background-color: #ddd !important;
    color: #111 !important;
}

.page-item.active .page-link {
    background-color: #ddd;
    border: 1px solid #ccc;
    color: #111;
}

.page-link:hover {
    color: #222 !important;
}
</style>
