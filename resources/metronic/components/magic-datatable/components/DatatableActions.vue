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
  -  *  Last modified: 12/12/24, 12:02 pm
  -  *  Written by Chintan Bagdawala, 2024.
  -  */
  -->

<template>
    <div class="d-flex align-items-center justify-content-between">
        <a
            v-for="a in rowActions"
            :class="a.class || ''"
            :title="a.label"
            class="dropdown-item is-media me-1 cursor-pointer"
            @click.prevent.stop="emit(a.event, { id: row[a.idField || 'id'] })"
        >
            <FormIcon :icon="a.icon" />
        </a>
        <RouterLink
            v-if="
                xprops.showType === 'link' &&
                $can(xprops.permission_prefix + 'show') &&
                showButton
            "
            :to="{
                name: `${xprops.route}.show`,
                params: { id: row.id },
            }"
            class="dropdown-item is-media me-1 cursor-pointer show-button"
            title="Details"
        >
            <FormIcon icon="feather:file-text" />
        </RouterLink>
        <RouterLink
            v-if="
                xprops.formType === 'link' &&
                $can(xprops.permission_prefix + 'edit') &&
                !restrictEdit
            "
            :to="getRoute('edit')"
            class="dropdown-item is-media me-1 cursor-pointer edit-button"
            title="Edit this item"
        >
            <FormIcon icon="feather:edit" />
        </RouterLink>
        <a
            v-if="
                xprops.formType === 'modal' &&
                $can(xprops.permission_prefix + 'edit') &&
                !restrictEdit
            "
            class="dropdown-item is-media me-1 cursor-pointer edit-button"
            title="Edit this item"
            @click.prevent.stop="emit(xprops.formClickAction, { id: row.id })"
        >
            <FormIcon icon="feather:edit" />
        </a>
        <a
            v-if="$can(xprops.permission_prefix + 'delete') && !restrictDelete"
            class="dropdown-item is-media cursor-pointer delete-button"
            title="Remove this item"
            @click.prevent.stop="destroyData(row.id, $event)"
        >
            <FormIcon icon="feather:trash-2" />
        </a>
    </div>
</template>

<script setup>
import emitter from "@/core/plugins/mitt";
import Swal from "sweetalert2";
import { computed } from "vue";

const props = defineProps({
    row: {
        type: Object,
        default: () => {},
    },
    xprops: {
        type: Object,
        default: () => {},
    },
});

const restrictEdit = computed(() => {
    return props.xprops.restrictEdit || false;
});

const restrictDelete = computed(() => {
    return props.xprops.restrictDelete || false;
});

const showButton = computed(() => {
    return _.get(props.xprops, "tableRowClick.enabled", true) === false;
});

const emit = (event, payload) => {
    emitter.emit(event, payload);
};

const rowActions = computed(() => {
    return (props.xprops && props.xprops.rowActions) || [];
});

const getRoute = (action) => {
    if (!props.row.id) {
        console.log("Row ID is not available for action:", props.row);
        return null;
    }
    if (props.xprops.formType === "link") {
        return {
            name: `${props.xprops.route}.${action}`,
            params: { id: props.row.id },
        };
    } else {
        return props.xprops.modals.form.route;
    }
};

const destroyData = (id, e) => {
    Swal.fire({
        title: "Are you sure?",
        text: "You won't be able to revert this!",
        type: "warning",
        showCancelButton: true,
        confirmButtonText: "Delete",
        confirmButtonColor: "#dd4b39",
        focusCancel: true,
        reverseButtons: true,
    }).then((r) => {
        if (!r.isConfirmed) return;
        emitter.emit("perform-item-delete", id);
    });
};
</script>
<style scoped>
a {
    font-size: 1.5rem;
}

.show-button {
    color: var(--bs-primary);
}

.edit-button {
    color: var(--bs-success);
}

.delete-button {
    color: var(--bs-danger);
}
</style>
