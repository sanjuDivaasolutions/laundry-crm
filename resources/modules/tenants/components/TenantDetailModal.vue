<template>
    <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5)">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        {{ tenant?.name }}
                        <span :class="statusBadgeClass" class="badge ms-2">
                            {{ tenant?.status_label }}
                        </span>
                    </h5>
                    <button type="button" class="btn-close" @click="$emit('close')"></button>
                </div>
                <div class="modal-body">
                    <div v-if="loading" class="text-center py-10">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                    <div v-else-if="details">
                        <!-- Tabs -->
                        <ul class="nav nav-tabs nav-line-tabs mb-5 fs-6">
                            <li class="nav-item">
                                <a class="nav-link" :class="{ active: activeTab === 'overview' }"
                                   @click="activeTab = 'overview'" href="#">Overview</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" :class="{ active: activeTab === 'users' }"
                                   @click="activeTab = 'users'" href="#">Users ({{ details.users?.length || 0 }})</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" :class="{ active: activeTab === 'subscription' }"
                                   @click="activeTab = 'subscription'" href="#">Subscription</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" :class="{ active: activeTab === 'usage' }"
                                   @click="activeTab = 'usage'" href="#">Usage</a>
                            </li>
                        </ul>

                        <!-- Overview Tab -->
                        <div v-if="activeTab === 'overview'" class="row">
                            <div class="col-md-6">
                                <h6 class="fw-bold mb-3">Tenant Information</h6>
                                <table class="table table-row-bordered">
                                    <tr>
                                        <td class="fw-semibold w-150px">ID</td>
                                        <td>{{ details.tenant.id }}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-semibold">Name</td>
                                        <td>{{ details.tenant.name }}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-semibold">Subdomain</td>
                                        <td>
                                            <a :href="details.tenant.url" target="_blank">
                                                {{ details.tenant.domain }}
                                            </a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="fw-semibold">Timezone</td>
                                        <td>{{ details.tenant.timezone }}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-semibold">Currency</td>
                                        <td>{{ details.tenant.currency }}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-semibold">Created</td>
                                        <td>{{ formatDate(details.tenant.created_at) }}</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h6 class="fw-bold mb-3">Status</h6>
                                <table class="table table-row-bordered">
                                    <tr>
                                        <td class="fw-semibold w-150px">Status</td>
                                        <td>
                                            <span :class="statusBadgeClass" class="badge">
                                                {{ details.tenant.status_label }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr v-if="details.tenant.trial_ends_at">
                                        <td class="fw-semibold">Trial Ends</td>
                                        <td>
                                            {{ formatDate(details.tenant.trial_ends_at) }}
                                            <span v-if="details.tenant.trial_days_remaining > 0" class="text-muted">
                                                ({{ details.tenant.trial_days_remaining }} days left)
                                            </span>
                                        </td>
                                    </tr>
                                    <tr v-if="details.tenant.grace_period_ends_at">
                                        <td class="fw-semibold">Grace Period Ends</td>
                                        <td class="text-warning">{{ formatDate(details.tenant.grace_period_ends_at) }}</td>
                                    </tr>
                                    <tr v-if="details.tenant.suspended_at">
                                        <td class="fw-semibold">Suspended At</td>
                                        <td class="text-danger">{{ formatDate(details.tenant.suspended_at) }}</td>
                                    </tr>
                                    <tr v-if="details.tenant.suspension_reason">
                                        <td class="fw-semibold">Reason</td>
                                        <td class="text-danger">{{ details.tenant.suspension_reason }}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-semibold">Current Plan</td>
                                        <td>{{ details.tenant.current_plan || 'Trial' }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <!-- Users Tab -->
                        <div v-if="activeTab === 'users'">
                            <table class="table table-row-bordered table-row-gray-100 align-middle gs-0 gy-3">
                                <thead>
                                    <tr class="fw-bold text-muted">
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Roles</th>
                                        <th>Verified</th>
                                        <th>Joined</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="user in details.users" :key="user.id">
                                        <td>{{ user.name }}</td>
                                        <td>{{ user.email }}</td>
                                        <td>
                                            <span v-for="role in user.roles" :key="role"
                                                  class="badge badge-light-primary me-1">
                                                {{ role }}
                                            </span>
                                        </td>
                                        <td>
                                            <i v-if="user.email_verified" class="ki-duotone ki-check-circle text-success fs-2"></i>
                                            <i v-else class="ki-duotone ki-cross-circle text-danger fs-2"></i>
                                        </td>
                                        <td>{{ formatDate(user.created_at) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Subscription Tab -->
                        <div v-if="activeTab === 'subscription'">
                            <div v-if="details.subscriptions?.length">
                                <table class="table table-row-bordered">
                                    <thead>
                                        <tr class="fw-bold text-muted">
                                            <th>Plan</th>
                                            <th>Status</th>
                                            <th>Started</th>
                                            <th>Ends At</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="sub in details.subscriptions" :key="sub.id">
                                            <td>{{ sub.name }}</td>
                                            <td>
                                                <span :class="getSubscriptionStatusClass(sub.stripe_status)"
                                                      class="badge">
                                                    {{ sub.stripe_status }}
                                                </span>
                                            </td>
                                            <td>{{ formatDate(sub.created_at) }}</td>
                                            <td>{{ sub.ends_at ? formatDate(sub.ends_at) : '-' }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div v-else class="text-center py-10 text-muted">
                                No subscription history
                            </div>
                        </div>

                        <!-- Usage Tab -->
                        <div v-if="activeTab === 'usage'">
                            <div class="row g-5">
                                <div class="col-md-3">
                                    <div class="border rounded p-5 text-center">
                                        <div class="fs-1 fw-bold text-primary">{{ details.usage.users }}</div>
                                        <div class="text-muted">Users</div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="border rounded p-5 text-center">
                                        <div class="fs-1 fw-bold text-info">{{ details.usage.items }}</div>
                                        <div class="text-muted">Items</div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="border rounded p-5 text-center">
                                        <div class="fs-1 fw-bold text-success">{{ details.usage.orders }}</div>
                                        <div class="text-muted">Orders</div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="border rounded p-5 text-center">
                                        <div class="fs-1 fw-bold text-warning">{{ details.usage.customers }}</div>
                                        <div class="text-muted">Customers</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <!-- Action Buttons -->
                    <div class="d-flex justify-content-between w-100">
                        <div>
                            <button
                                v-if="details?.tenant?.active"
                                class="btn btn-light-danger me-2"
                                @click="suspendTenant"
                                :disabled="actionLoading"
                            >
                                Suspend
                            </button>
                            <button
                                v-else
                                class="btn btn-light-success me-2"
                                @click="reactivateTenant"
                                :disabled="actionLoading"
                            >
                                Reactivate
                            </button>
                            <button
                                v-if="details?.tenant?.trial_days_remaining !== undefined"
                                class="btn btn-light-info me-2"
                                @click="showExtendTrialModal = true"
                                :disabled="actionLoading"
                            >
                                Extend Trial
                            </button>
                            <a
                                :href="details?.tenant?.url"
                                target="_blank"
                                class="btn btn-light-primary"
                            >
                                Visit Tenant
                            </a>
                        </div>
                        <button type="button" class="btn btn-secondary" @click="$emit('close')">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Extend Trial Modal -->
    <div v-if="showExtendTrialModal" class="modal fade show d-block" style="background: rgba(0,0,0,0.5)">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Extend Trial</h5>
                    <button type="button" class="btn-close" @click="showExtendTrialModal = false"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label">Days to add</label>
                    <input type="number" class="form-control" v-model="extendDays" min="1" max="90">
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" @click="showExtendTrialModal = false">Cancel</button>
                    <button class="btn btn-primary" @click="extendTrial" :disabled="actionLoading">
                        Extend
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import axios from "axios";
import Swal from "sweetalert2";

const props = defineProps({
    tenant: Object
});

const emit = defineEmits(["close", "refresh"]);

const loading = ref(true);
const actionLoading = ref(false);
const details = ref(null);
const activeTab = ref("overview");
const showExtendTrialModal = ref(false);
const extendDays = ref(7);

const statusBadgeClass = computed(() => {
    const status = details.value?.tenant?.status || props.tenant?.status;
    return {
        'badge-light-success': status === 'active',
        'badge-light-info': status === 'trial',
        'badge-light-warning': status === 'expired' || status === 'grace_period',
        'badge-light-danger': status === 'suspended',
    };
});

const loadDetails = async () => {
    loading.value = true;
    try {
        const response = await axios.get(`/api/v1/admin/tenants/${props.tenant.id}`);
        details.value = response.data;
    } catch (error) {
        console.error("Failed to load tenant details:", error);
        Swal.fire("Error", "Failed to load tenant details", "error");
    } finally {
        loading.value = false;
    }
};

const suspendTenant = async () => {
    const { value: reason } = await Swal.fire({
        title: "Suspend Tenant",
        input: "textarea",
        inputLabel: "Reason for suspension",
        inputPlaceholder: "Enter reason...",
        showCancelButton: true,
        inputValidator: (value) => {
            if (!value) return "Please enter a reason";
        }
    });

    if (reason) {
        actionLoading.value = true;
        try {
            await axios.post(`/api/v1/admin/tenants/${props.tenant.id}/suspend`, { reason });
            Swal.fire("Success", "Tenant suspended", "success");
            await loadDetails();
            emit("refresh");
        } catch (error) {
            Swal.fire("Error", error.response?.data?.message || "Failed to suspend", "error");
        } finally {
            actionLoading.value = false;
        }
    }
};

const reactivateTenant = async () => {
    const confirm = await Swal.fire({
        title: "Reactivate Tenant?",
        text: "This will restore access for the tenant.",
        icon: "question",
        showCancelButton: true,
        confirmButtonText: "Yes, reactivate"
    });

    if (confirm.isConfirmed) {
        actionLoading.value = true;
        try {
            await axios.post(`/api/v1/admin/tenants/${props.tenant.id}/reactivate`);
            Swal.fire("Success", "Tenant reactivated", "success");
            await loadDetails();
            emit("refresh");
        } catch (error) {
            Swal.fire("Error", error.response?.data?.message || "Failed to reactivate", "error");
        } finally {
            actionLoading.value = false;
        }
    }
};

const extendTrial = async () => {
    actionLoading.value = true;
    try {
        await axios.post(`/api/v1/admin/tenants/${props.tenant.id}/extend-trial`, {
            days: extendDays.value
        });
        Swal.fire("Success", `Trial extended by ${extendDays.value} days`, "success");
        showExtendTrialModal.value = false;
        await loadDetails();
        emit("refresh");
    } catch (error) {
        Swal.fire("Error", error.response?.data?.message || "Failed to extend trial", "error");
    } finally {
        actionLoading.value = false;
    }
};

const formatDate = (dateStr) => {
    if (!dateStr) return "-";
    return new Date(dateStr).toLocaleDateString("en-US", {
        year: "numeric",
        month: "short",
        day: "numeric"
    });
};

const getSubscriptionStatusClass = (status) => {
    return {
        'badge-light-success': status === 'active',
        'badge-light-warning': status === 'past_due' || status === 'trialing',
        'badge-light-danger': status === 'canceled' || status === 'unpaid',
    };
};

onMounted(() => {
    loadDetails();
});
</script>
