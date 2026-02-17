<!--
  - /*
  -  *  Copyright (c) 2025 Divaa Solutions. All rights reserved.
  -  *
  -  *  This software is the confidential and proprietary information of Divaa Solutions
  -  *  ("Confidential Information"). You shall not disclose such Confidential Information and
  -  *  shall use it only in accordance with the terms of the license agreement you entered into
  -  *  with Divaa Solutions.
  -  *
  -  *  Unauthorized copying of this file, via any medium is strictly prohibited.
  -  *  Proprietary and confidential.
  -  *
  -  *  Last modified: 07/01/25, 5:49 pm
  -  *  Written by Chintan Bagdawala, 2025.
  -  */
  -->

<template>
    <div v-if="enabled" class="d-flex align-items-center">
        <div class="d-flex border rounded px-3 py-2 fs-7">
            <div class="d-flex flex-column me-5">
                <small>Active Company</small>
                <strong>{{ activeCompanyName }}</strong>
            </div>
            <div class="d-flex align-items-center">
                <button
                  v-if="hasManyCompanies"
                    href="#"
                    @click.prevent="initChangeCompany"
                    class="btn btn-sm btn-secondary"
                    :disabled="restrictEdit"
                >
                    Change
                </button>
            </div>
        </div>
    </div>
    <EasyModalContainer
        id="change-company-modal"
        title="Set Company"
        size="xl"
        :centered="true"
        :backdrop="true"
        :narrow-padding="true"
        :show="showModal"
        @close="modal.show = false"
    >
        <template #body>
            <div
                v-for="c in companies"
                :key="c.id"
                class="w-100 d-flex align-items-center justify-content-between border rounded px-3 py-2 fs-7 mb-3"
            >
                <h5 class="mb-0">{{ c.name }}</h5>
                <a
                    href="#"
                    @click.prevent="selectCompany(c)"
                    class="btn btn-sm btn-danger"
                    >Set</a
                >
            </div>
        </template>
    </EasyModalContainer>
</template>

<script setup>
import { computed, onMounted, ref } from "vue";
import { useAuthStore } from "@/stores/auth";
import EasyModalContainer from "@common@/modals/EasyModalContainer.vue";
import ApiService from "@/core/services/ApiService";
import emitter from "@/core/plugins/mitt";

const authStore = useAuthStore();

const props = defineProps({
    enabled: {
        type: Boolean,
        default: true,
    },
});

const getUserCompanyId = () => {
    return authStore.getUserSetting("company_id", 1);
};

const company = ref({});
const companyId = computed(() => getUserCompanyId());

const activeCompanyName = computed(() => company.value?.name ?? '');

const modal = ref({
    show: false,
});

const hasManyCompanies = computed(() => companies.value.length > 1);

const restrictEdit = computed(() => authStore.restrictCompanyChange);

const showModal = computed(() => modal.value.show);
const companies = ref([]);

const initChangeCompany = async () => {
    modal.value.show = true;
};

const fetchCompanies = async () => {
    return new Promise((resolve, reject) => {
        ApiService.get("/bulk-options/companies").then((response) => {
            companies.value = _.get(response, "data.data.companies", []);
            resolve(response);
        });
    });
};

const setCompanyById = (id) => {
    const c = companies.value.find((c) => c.id === id);
    setCompany(c);
};

const setCompany = (obj) => {
    company.value = obj;
};

const selectCompany = async (c) => {
    setCompany(c);
    await authStore.updateUserSetting("company_id", c.id);
    modal.value.show = false;
    emitter.emit("user-company-changed", c);
};

onMounted(async () => {
    if (!props.enabled) {
        return;
    }
    await fetchCompanies();
    setCompanyById(companyId.value);
});
</script>

<style scoped></style>
