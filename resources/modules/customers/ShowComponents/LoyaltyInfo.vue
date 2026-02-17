<template>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Loyalty Program</h3>
        </div>
        <div class="card-body">
            <!-- Loyalty Summary -->
            <div class="row mb-6">
                <div class="col-sm-6 col-lg-3 mb-4">
                    <div class="border rounded p-4 text-center">
                        <div class="text-muted fs-7 mb-1">Current Points</div>
                        <div class="fw-bold fs-2 text-primary">{{ loyaltyData.points || 0 }}</div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3 mb-4">
                    <div class="border rounded p-4 text-center">
                        <div class="text-muted fs-7 mb-1">Tier</div>
                        <div class="fw-bold fs-2">
                            <span :class="tierClass">{{ tierLabel }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3 mb-4">
                    <div class="border rounded p-4 text-center">
                        <div class="text-muted fs-7 mb-1">Total Orders</div>
                        <div class="fw-bold fs-2">{{ loyaltyData.total_orders_count || 0 }}</div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3 mb-4">
                    <div class="border rounded p-4 text-center">
                        <div class="text-muted fs-7 mb-1">Total Spent</div>
                        <div class="fw-bold fs-2 text-success">{{ formatCurrency(loyaltyData.total_spent) }}</div>
                    </div>
                </div>
            </div>

            <!-- Tier Progress -->
            <div class="mb-6" v-if="loyaltyData.next_tier">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Progress to {{ loyaltyData.next_tier }}</span>
                    <span class="fw-semibold">{{ loyaltyData.points_to_next_tier || 0 }} points needed</span>
                </div>
                <div class="progress" style="height: 8px;">
                    <div
                        class="progress-bar"
                        :class="tierProgressClass"
                        role="progressbar"
                        :style="{ width: tierProgress + '%' }"
                    ></div>
                </div>
            </div>

            <!-- Transaction History -->
            <h5 class="mb-4 mt-6">Transaction History</h5>
            <div class="table-responsive" v-if="transactions.length > 0">
                <table class="table table-row-bordered gy-3">
                    <thead>
                        <tr class="fw-bold text-muted bg-light">
                            <th>Date</th>
                            <th>Type</th>
                            <th>Description</th>
                            <th class="text-end">Points</th>
                            <th class="text-end">Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="t in transactions" :key="t.id">
                            <td>{{ formatDate(t.created_at) }}</td>
                            <td>
                                <span :class="typeClass(t.type)" class="badge">{{ t.type }}</span>
                            </td>
                            <td>{{ t.description }}</td>
                            <td class="text-end" :class="t.points > 0 ? 'text-success' : 'text-danger'">
                                {{ t.points > 0 ? '+' : '' }}{{ t.points }}
                            </td>
                            <td class="text-end">{{ t.balance_after }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div v-else class="text-center text-muted py-5">
                No loyalty transactions yet.
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import ApiService from "@/core/services/ApiService";

const props = defineProps({
    customerId: { type: [Number, String], required: true },
    customer: { type: Object, default: () => ({}) },
});

const transactions = ref([]);
const loyaltyData = ref({});

const tierLabel = computed(() => {
    const tier = loyaltyData.value.loyalty_tier || props.customer?.loyalty_tier || "bronze";
    return tier.charAt(0).toUpperCase() + tier.slice(1);
});

const tierClass = computed(() => {
    const tier = loyaltyData.value.loyalty_tier || props.customer?.loyalty_tier || "bronze";
    return {
        bronze: "text-warning",
        silver: "text-gray-500",
        gold: "text-warning",
        platinum: "text-info",
    }[tier] || "text-muted";
});

const tierProgress = computed(() => {
    const tiers = { bronze: 0, silver: 500, gold: 2000, platinum: 5000 };
    const current = loyaltyData.value.points || 0;
    const tier = loyaltyData.value.loyalty_tier || "bronze";
    const nextTierPoints = { bronze: 500, silver: 2000, gold: 5000, platinum: 99999 };
    const currentTierMin = tiers[tier] || 0;
    const nextTierMin = nextTierPoints[tier] || 99999;
    const range = nextTierMin - currentTierMin;
    if (range <= 0) return 100;
    return Math.min(100, Math.round(((current - currentTierMin) / range) * 100));
});

const tierProgressClass = computed(() => {
    const tier = loyaltyData.value.loyalty_tier || "bronze";
    return {
        bronze: "bg-warning",
        silver: "bg-secondary",
        gold: "bg-warning",
        platinum: "bg-info",
    }[tier] || "bg-primary";
});

const formatCurrency = (amount) => {
    return parseFloat(amount || 0).toLocaleString("en-US", { style: "currency", currency: "USD" });
};

const formatDate = (date) => {
    if (!date) return "-";
    return new Date(date).toLocaleDateString("en-US", { year: "numeric", month: "short", day: "numeric" });
};

const typeClass = (type) => {
    return {
        earned: "badge-light-success",
        redeemed: "badge-light-danger",
        bonus: "badge-light-info",
        expired: "badge-light-warning",
        adjustment: "badge-light-primary",
    }[type] || "badge-light-secondary";
};

const loadLoyaltyData = async () => {
    try {
        const response = await ApiService.get(`customers/${props.customerId}`);
        const customer = response.data.data || response.data;
        loyaltyData.value = {
            points: customer.loyalty_points || 0,
            loyalty_tier: customer.loyalty_tier || "bronze",
            total_orders_count: customer.total_orders_count || 0,
            total_spent: customer.total_spent || 0,
            next_tier: getNextTier(customer.loyalty_tier || "bronze"),
            points_to_next_tier: getPointsToNextTier(customer.loyalty_points || 0, customer.loyalty_tier || "bronze"),
        };

        // Load transactions from loyalty_transactions if customer has them
        transactions.value = customer.loyalty_transactions || [];
    } catch (error) {
        console.error("Error loading loyalty data:", error);
    }
};

const getNextTier = (currentTier) => {
    const order = ["bronze", "silver", "gold", "platinum"];
    const idx = order.indexOf(currentTier);
    return idx < order.length - 1 ? order[idx + 1].charAt(0).toUpperCase() + order[idx + 1].slice(1) : null;
};

const getPointsToNextTier = (points, tier) => {
    const thresholds = { bronze: 500, silver: 2000, gold: 5000, platinum: 99999 };
    return Math.max(0, (thresholds[tier] || 0) - points);
};

onMounted(() => {
    loadLoyaltyData();
});
</script>
