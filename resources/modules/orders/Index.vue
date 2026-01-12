<template>
  <div class="card card-custom">
    <div class="card-header">
      <div class="card-title">
        <h3 class="card-label">
          {{ $t('Sales Orders') }}
          <span class="d-block text-muted pt-2 font-size-sm">
            {{ $t('Manage your sales orders and track their status') }}
          </span>
        </h3>
      </div>
      <div class="card-toolbar">
        <div class="example-tools justify-content-center">
          <span class="example-toggle" data-bs-toggle="tooltip" title="View details"></span>
          <span class="example-copy" data-bs-toggle="tooltip" title="Copy code"></span>
        </div>
      </div>
    </div>
    <div class="card-body">
      <!-- Search and Filters -->
      <div class="row mb-7">
        <div class="col-lg-3">
          <input
            type="text"
            class="form-control form-control-solid"
            :placeholder="$t('Search by order number or buyer...')"
            v-model="searchQuery"
            @input="debouncedSearch"
          />
        </div>
        <div class="col-lg-2">
          <select
            class="form-select form-select-solid"
            v-model="filters.status"
            @change="loadOrders"
          >
            <option value="">{{ $t('All Status') }}</option>
            <option value="draft">{{ $t('Draft') }}</option>
            <option value="pending">{{ $t('Pending') }}</option>
            <option value="confirmed">{{ $t('Confirmed') }}</option>
            <option value="converted">{{ $t('Converted') }}</option>
            <option value="cancelled">{{ $t('Cancelled') }}</option>
          </select>
        </div>
        <div class="col-lg-2">
          <select
            class="form-select form-select-solid"
            v-model="filters.buyer_id"
            @change="loadOrders"
          >
            <option value="">{{ $t('All Buyers') }}</option>
            <option v-for="buyer in buyers" :key="buyer.id" :value="buyer.id">
              {{ buyer.display_name }}
            </option>
          </select>
        </div>
        <div class="col-lg-2">
          <select
            class="form-select form-select-solid"
            v-model="filters.agent_id"
            @change="loadOrders"
          >
            <option value="">{{ $t('All Agents') }}</option>
            <option v-for="agent in agents" :key="agent.id" :value="agent.id">
              {{ agent.name }}
            </option>
          </select>
        </div>
        <div class="col-lg-3">
          <div class="d-flex justify-content-end">
            <button
              class="btn btn-primary me-2"
              @click="showCreateModal = true"
            >
              <i class="bi bi-plus-lg"></i>
              {{ $t('New Order') }}
            </button>
            <button
              class="btn btn-light-primary"
              @click="exportOrders"
            >
              <i class="bi bi-download"></i>
              {{ $t('Export') }}
            </button>
          </div>
        </div>
      </div>

      <!-- Orders Table -->
      <div class="table-responsive">
        <table class="table table-row-dashed table-row-gray-100 align-middle gy-4">
          <thead>
            <tr class="fw-bolder text-muted">
              <th class="w-70px">{{ $t('ID') }}</th>
              <th>{{ $t('Order Number') }}</th>
              <th>{{ $t('Buyer') }}</th>
              <th>{{ $t('Agent') }}</th>
              <th>{{ $t('Date') }}</th>
              <th>{{ $t('Total') }}</th>
              <th>{{ $t('Status') }}</th>
              <th class="text-end min-w-100px">{{ $t('Actions') }}</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="loading">
              <td colspan="8" class="text-center py-8">
                <div class="spinner-border spinner-border-lg" role="status">
                  <span class="visually-hidden">{{ $t('Loading...') }}</span>
                </div>
              </td>
            </tr>
            <tr v-else-if="orders.length === 0">
              <td colspan="8" class="text-center py-8">
                <div class="text-muted">
                  {{ $t('No orders found') }}
                </div>
              </td>
            </tr>
            <tr v-for="order in orders" :key="order.id" v-else>
              <td>{{ order.id }}</td>
              <td>
                <span class="text-dark fw-bolder text-hover-primary mb-1">
                  {{ order.order_number }}
                </span>
              </td>
              <td>
                <span v-if="order.buyer">
                  {{ order.buyer.display_name }}
                </span>
              </td>
              <td>
                <span v-if="order.agent" class="badge badge-light-primary">
                  {{ order.agent.name }}
                </span>
                <span v-else class="badge badge-light">
                  {{ $t('No Agent') }}
                </span>
              </td>
              <td>{{ formatDate(order.date) }}</td>
              <td class="text-end">
                <span class="fw-bolder">
                  {{ formatCurrency(order.grand_total) }}
                </span>
              </td>
              <td>
                <span :class="getStatusClass(order.status)">
                  {{ $t(order.status.charAt(0).toUpperCase() + order.status.slice(1)) }}
                </span>
              </td>
              <td class="text-end">
                <div class="d-flex justify-content-end flex-shrink-0">
                  <button
                    class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1"
                    @click="viewOrder(order)"
                    :title="$t('View')"
                  >
                    <i class="bi bi-eye"></i>
                  </button>
                  <button
                    class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1"
                    @click="editOrder(order)"
                    :title="$t('Edit')"
                  >
                    <i class="bi bi-pencil"></i>
                  </button>
                  <button
                    v-if="order.status === 'confirmed'"
                    class="btn btn-icon btn-bg-light btn-active-color-success btn-sm me-1"
                    @click="convertToInvoice(order)"
                    :title="$t('Convert to Invoice')"
                  >
                    <i class="bi bi-file-earmark-text"></i>
                  </button>
                  <button
                    class="btn btn-icon btn-bg-light btn-active-color-danger btn-sm"
                    @click="deleteOrder(order)"
                    :title="$t('Delete')"
                  >
                    <i class="bi bi-trash"></i>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div class="d-flex justify-content-between align-items-center mt-8">
        <div class="d-flex align-items-center">
          <span class="text-muted">
            {{ $t('Showing') }} {{ pagination.from }} {{ $t('to') }} {{ pagination.to }} {{ $t('of') }} {{ pagination.total }} {{ $t('entries') }}
          </span>
        </div>
        <div class="d-flex align-items-center">
          <select
            class="form-select form-select-sm me-4"
            v-model="pagination.per_page"
            @change="loadOrders"
          >
            <option value="10">10</option>
            <option value="25">25</option>
            <option value="50">50</option>
            <option value="100">100</option>
          </select>
          <Pagination
            :current-page="pagination.current_page"
            :total-pages="pagination.last_page"
            @page-changed="changePage"
          />
        </div>
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <OrderModal
      v-if="showCreateModal"
      :order="selectedOrder"
      :buyers="buyers"
      :agents="agents"
      @close="showCreateModal = false"
      @saved="onOrderSaved"
    />

    <!-- View Modal -->
    <OrderViewModal
      v-if="showViewModal"
      :order="selectedOrder"
      @close="showViewModal = false"
      @edit="editOrder"
      @convert="convertToInvoice"
    />
  </div>
</template>

<script lang="ts">
import { defineComponent, ref, onMounted, computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { debounce } from 'lodash'
import { useOrderStore } from '@/modules/orders/stores/OrderStore'
import { useBuyerStore } from '@/modules/buyers/stores/BuyerStore'
import { useAgentStore } from '@/modules/agents/stores/AgentStore'
import OrderModal from './OrderModal.vue'
import OrderViewModal from './OrderViewModal.vue'
import Pagination from '@/common/components/Pagination.vue'
import { formatDate, formatCurrency } from '@/utility/DateUtils'

export default defineComponent({
  name: 'OrderIndex',
  components: {
    OrderModal,
    OrderViewModal,
    Pagination
  },
  setup() {
    const { t } = useI18n()
    const orderStore = useOrderStore()
    const buyerStore = useBuyerStore()
    const agentStore = useAgentStore()

    const loading = ref(false)
    const searchQuery = ref('')
    const showCreateModal = ref(false)
    const showViewModal = ref(false)
    const selectedOrder = ref(null)

    const filters = ref({
      status: '',
      buyer_id: '',
      agent_id: ''
    })

    const pagination = ref({
      current_page: 1,
      last_page: 1,
      per_page: 25,
      total: 0,
      from: 0,
      to: 0
    })

    const orders = computed(() => orderStore.orders)
    const buyers = computed(() => buyerStore.buyers)
    const agents = computed(() => agentStore.agents)

    const debouncedSearch = debounce(() => {
      loadOrders()
    }, 500)

    const loadOrders = async () => {
      loading.value = true
      try {
        const params = {
          page: pagination.value.current_page,
          per_page: pagination.value.per_page,
          search: searchQuery.value,
          ...filters.value
        }
        
        await orderStore.fetchOrders(params)
        
        const orderData = orderStore.orderData
        pagination.value = {
          current_page: orderData.current_page,
          last_page: orderData.last_page,
          per_page: orderData.per_page,
          total: orderData.total,
          from: orderData.from || 0,
          to: orderData.to || 0
        }
      } catch (error) {
        console.error('Error loading orders:', error)
      } finally {
        loading.value = false
      }
    }

    const changePage = (page: number) => {
      pagination.value.current_page = page
      loadOrders()
    }

    const viewOrder = (order: any) => {
      selectedOrder.value = order
      showViewModal.value = true
    }

    const editOrder = (order: any) => {
      selectedOrder.value = order
      showViewModal.value = false
      showCreateModal.value = true
    }

    const convertToInvoice = async (order: any) => {
      try {
        await orderStore.convertToInvoice(order.id)
        // Show success message
        await loadOrders()
      } catch (error) {
        console.error('Error converting to invoice:', error)
      }
    }

    const deleteOrder = async (order: any) => {
      if (confirm(t('Are you sure you want to delete this order?'))) {
        try {
          await orderStore.deleteOrder(order.id)
          await loadOrders()
        } catch (error) {
          console.error('Error deleting order:', error)
        }
      }
    }

    const exportOrders = async () => {
      try {
        await orderStore.exportOrders(filters.value)
      } catch (error) {
        console.error('Error exporting orders:', error)
      }
    }

    const onOrderSaved = () => {
      showCreateModal.value = false
      selectedOrder.value = null
      loadOrders()
    }

    const getStatusClass = (status: string) => {
      const statusClasses = {
        draft: 'badge badge-light-warning',
        pending: 'badge badge-light-info',
        confirmed: 'badge badge-light-primary',
        converted: 'badge badge-light-success',
        cancelled: 'badge badge-light-danger'
      }
      return statusClasses[status] || 'badge badge-light'
    }

    onMounted(async () => {
      await Promise.all([
        buyerStore.fetchBuyers(),
        agentStore.fetchAgents(),
        loadOrders()
      ])
    })

    return {
      loading,
      searchQuery,
      filters,
      pagination,
      orders,
      buyers,
      agents,
      showCreateModal,
      showViewModal,
      selectedOrder,
      debouncedSearch,
      loadOrders,
      changePage,
      viewOrder,
      editOrder,
      convertToInvoice,
      deleteOrder,
      exportOrders,
      onOrderSaved,
      getStatusClass,
      formatDate,
      formatCurrency,
      t
    }
  }
})
</script>