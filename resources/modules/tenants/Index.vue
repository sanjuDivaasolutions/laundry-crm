<template>
    <div class="card">
        <!-- Header -->
        <div class="card-header border-0 pt-6">
            <div class="card-title">
                <h2>Tenant Management</h2>
            </div>
            <div class="card-toolbar">
                <!-- Statistics Button -->
                <button
                    class="btn btn-sm btn-light-primary me-3"
                    @click="showStatistics = !showStatistics"
                >
                    <i class="ki-duotone ki-chart-simple fs-2">
                        <span class="path1"></span>
                        <span class="path2"></span>
                        <span class="path3"></span>
                        <span class="path4"></span>
                    </i>
                    Statistics
                </button>
            </div>
        </div>

        <!-- Statistics Panel -->
        <div v-if="showStatistics" class="card-body pt-0">
            <div class="row g-5 g-xl-8">
                <div class="col-xl-3">
                    <div class="card bg-light-primary">
                        <div class="card-body py-4">
                            <div class="d-flex align-items-center">
                                <div class="symbol symbol-45px me-4">
                                    <span class="symbol-label bg-primary">
                                        <i class="ki-duotone ki-home-2 fs-2x text-white"></i>
                                    </span>
                                </div>
                                <div>
                                    <div class="fs-2 fw-bold">{{ stats.total_tenants || 0 }}</div>
                                    <div class="fs-7 text-muted">Total Tenants</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3">
                    <div class="card bg-light-success">
                        <div class="card-body py-4">
                            <div class="d-flex align-items-center">
                                <div class="symbol symbol-45px me-4">
                                    <span class="symbol-label bg-success">
                                        <i class="ki-duotone ki-check fs-2x text-white"></i>
                                    </span>
                                </div>
                                <div>
                                    <div class="fs-2 fw-bold">{{ stats.active_tenants || 0 }}</div>
                                    <div class="fs-7 text-muted">Active Tenants</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3">
                    <div class="card bg-light-info">
                        <div class="card-body py-4">
                            <div class="d-flex align-items-center">
                                <div class="symbol symbol-45px me-4">
                                    <span class="symbol-label bg-info">
                                        <i class="ki-duotone ki-timer fs-2x text-white"></i>
                                    </span>
                                </div>
                                <div>
                                    <div class="fs-2 fw-bold">{{ stats.tenants_on_trial || 0 }}</div>
                                    <div class="fs-7 text-muted">On Trial</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3">
                    <div class="card bg-light-warning">
                        <div class="card-body py-4">
                            <div class="d-flex align-items-center">
                                <div class="symbol symbol-45px me-4">
                                    <span class="symbol-label bg-warning">
                                        <i class="ki-duotone ki-dollar fs-2x text-white"></i>
                                    </span>
                                </div>
                                <div>
                                    <div class="fs-2 fw-bold">{{ stats.paying_tenants || 0 }}</div>
                                    <div class="fs-7 text-muted">Paying</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="card-body pt-0">
            <IndexModule
                :index-store="moduleIndexStore"
                :form-store="moduleFormStore"
            />
        </div>

        <!-- Tenant Detail Modal -->
        <TenantDetailModal
            v-if="selectedTenant"
            :tenant="selectedTenant"
            @close="selectedTenant = null"
            @refresh="loadTenants"
        />
    </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import IndexModule from "@common@/components/IndexModule.vue";
import { useModuleIndexStore } from "@modules@/tenants/tenantsIndexStore";
import TenantDetailModal from "./components/TenantDetailModal.vue";
import axios from "axios";

const moduleIndexStore = useModuleIndexStore();
const moduleFormStore = null; // No form store for tenants (read-only list)

const showStatistics = ref(false);
const stats = ref({});
const selectedTenant = ref(null);

const loadStatistics = async () => {
    try {
        const response = await axios.get("/api/v1/admin/tenants/statistics");
        stats.value = response.data;
    } catch (error) {
        console.error("Failed to load statistics:", error);
    }
};

const loadTenants = () => {
    // Trigger table refresh
    moduleIndexStore.$reset();
};

onMounted(() => {
    loadStatistics();
});
</script>

<style scoped></style>
