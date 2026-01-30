import { defineStore } from "pinia";
import { ref, computed } from "vue";
import ApiService from "@/core/services/ApiService";

export const usePosStore = defineStore("pos", () => {
    // State
    const orders = ref([]);
    const items = ref([]);
    const services = ref([]);
    const statuses = ref([]);
    const statistics = ref({
        pending: 0,
        washing: 0,
        drying: 0,
        ready: 0,
        completed: 0,
        today_revenue: 0,
    });
    const loading = ref(false);
    const selectedOrderId = ref(null);

    // Getters
    const selectedOrder = computed(() =>
        orders.value.find((o) => o.id === selectedOrderId.value)
    );

    const ordersByStatus = computed(() => {
        const grouped = {};
        orders.value.forEach((order) => {
            const statusId = order.processing_status_id;
            if (!grouped[statusId]) grouped[statusId] = [];
            grouped[statusId].push(order);
        });
        return grouped;
    });

    const getOrdersByStatusId = (statusId) => {
        return orders.value.filter((o) => o.processing_status_id === statusId);
    };

    // Actions
    async function fetchBoardData() {
        loading.value = true;
        try {
            const response = await ApiService.get("pos/board");
            if (response.data.success) {
                const data = response.data.data;
                statuses.value = data.statuses || [];
                items.value = data.items || [];
                services.value = data.services || [];
                statistics.value = data.statistics || {};

                // Flatten orders from grouped data
                orders.value = [];
                if (data.orders_by_status) {
                    Object.values(data.orders_by_status).forEach((statusOrders) => {
                        orders.value.push(...statusOrders);
                    });
                }
            }
        } catch (error) {
            console.error("Failed to fetch board data:", error);
        } finally {
            loading.value = false;
        }
    }

    async function createOrder(orderData) {
        try {
            // Pass false to skip stringifyArrays - Laravel expects actual arrays
            const response = await ApiService.post("pos/orders", orderData, false);
            if (response.data.success) {
                orders.value.unshift(response.data.data);
                await refreshStatistics();
                return response.data.data;
            }
        } catch (error) {
            console.error("Failed to create order:", error);
            throw error;
        }
    }

    async function updateOrderStatus(orderId, newStatusId) {
        try {
            const response = await ApiService.put(`pos/orders/${orderId}/status`, {
                processing_status_id: newStatusId,
            });
            if (response.data.success) {
                const index = orders.value.findIndex((o) => o.id === orderId);
                if (index !== -1) {
                    orders.value[index] = response.data.data;
                }
                await refreshStatistics();
                return response.data.data;
            }
        } catch (error) {
            console.error("Failed to update status:", error);
            throw error;
        }
    }

    async function processPayment(orderId, paymentData) {
        try {
            const response = await ApiService.post(
                `pos/orders/${orderId}/pay`,
                paymentData
            );
            if (response.data.success) {
                // Remove from board if delivered (status 6)
                const order = response.data.data.order;
                if (order.processing_status_id === 6) {
                    orders.value = orders.value.filter((o) => o.id !== orderId);
                } else {
                    const index = orders.value.findIndex((o) => o.id === orderId);
                    if (index !== -1) {
                        orders.value[index] = order;
                    }
                }
                selectedOrderId.value = null;
                await refreshStatistics();
                return response.data.data;
            }
        } catch (error) {
            console.error("Failed to process payment:", error);
            throw error;
        }
    }

    async function fetchOrderDetail(orderId) {
        try {
            const response = await ApiService.get(`pos/orders/${orderId}`);
            if (response.data.success) {
                return response.data.data;
            }
        } catch (error) {
            console.error("Failed to fetch order detail:", error);
            throw error;
        }
    }

    async function refreshStatistics() {
        try {
            const response = await ApiService.get("pos/statistics");
            if (response.data.success) {
                statistics.value = response.data.data;
            }
        } catch (error) {
            console.error("Failed to refresh statistics:", error);
        }
    }

    function selectOrder(orderId) {
        selectedOrderId.value = orderId;
    }

    function clearSelection() {
        selectedOrderId.value = null;
    }

    async function cancelOrder(orderId) {
        try {
            await ApiService.delete(`pos/orders/${orderId}`);
            orders.value = orders.value.filter((o) => o.id !== orderId);
            selectedOrderId.value = null;
            await refreshStatistics();
        } catch (error) {
            console.error("Failed to cancel order:", error);
            throw error;
        }
    }

    return {
        // State
        orders,
        items,
        services,
        statuses,
        statistics,
        loading,
        selectedOrderId,
        // Getters
        selectedOrder,
        ordersByStatus,
        getOrdersByStatusId,
        // Actions
        fetchBoardData,
        createOrder,
        updateOrderStatus,
        processPayment,
        fetchOrderDetail,
        refreshStatistics,
        selectOrder,
        clearSelection,
        cancelOrder,
    };
});
