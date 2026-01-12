import { defineStore } from 'pinia'
import { api } from '@/services/api'
import { showSuccessMessage, showErrorMessage } from '@/utility/NotificationUtils'

export interface Order {
  id: number
  order_number: string
  buyer_id: number
  buyer?: {
    id: number
    code: string
    display_name: string
    name: string
  }
  agent_id?: number
  agent?: {
    id: number
    code: string
    name: string
    email: string
  }
  company_id: number
  user_id: number
  order_type: string
  date: string
  due_date?: string
  sub_total: number
  tax_total: number
  grand_total: number
  tax_rate: number
  is_taxable: boolean
  status: string
  notes?: string
  commission?: number
  commission_total: number
  items: OrderItem[]
  commissions: Commission[]
  created_at: string
  updated_at: string
}

export interface OrderItem {
  id: number
  product_id: number
  product?: {
    id: number
    name: string
    code: string
    sku: string
  }
  description?: string
  quantity: number
  unit_price: number
  tax_rate: number
  tax_total: number
  sub_total: number
  grand_total: number
}

export interface Commission {
  id: number
  commission_amount: number
  commission_rate: number
  commission_type: string
  status: string
  commission_date: string
  paid_date?: string
  notes?: string
}

export interface OrderFilters {
  search?: string
  status?: string
  buyer_id?: number
  agent_id?: number
  page?: number
  per_page?: number
}

export const useOrderStore = defineStore('orders', {
  state: () => ({
    orders: [] as Order[],
    orderData: {
      current_page: 1,
      last_page: 1,
      per_page: 25,
      total: 0,
      from: 0,
      to: 0
    },
    loading: false,
    currentOrder: null as Order | null
  }),

  getters: {
    totalOrders: (state) => state.orderData.total,
    currentPage: (state) => state.orderData.current_page,
    lastPage: (state) => state.orderData.last_page,
    
    ordersByStatus: (state) => (status: string) => {
      return state.orders.filter(order => order.status === status)
    },
    
    ordersByAgent: (state) => (agentId: number) => {
      return state.orders.filter(order => order.agent_id === agentId)
    },
    
    ordersByBuyer: (state) => (buyerId: number) => {
      return state.orders.filter(order => order.buyer_id === buyerId)
    }
  },

  actions: {
    async fetchOrders(filters: OrderFilters = {}) {
      this.loading = true
      try {
        const response = await api.get('/v1/sales-orders', { params: filters })
        this.orders = response.data.data
        this.orderData = response.data
        return response.data
      } catch (error) {
        showErrorMessage('Failed to fetch orders')
        throw error
      } finally {
        this.loading = false
      }
    },

    async fetchOrder(id: number) {
      this.loading = true
      try {
        const response = await api.get(`/v1/sales-orders/${id}`)
        this.currentOrder = response.data
        return response.data
      } catch (error) {
        showErrorMessage('Failed to fetch order')
        throw error
      } finally {
        this.loading = false
      }
    },

    async createOrder(orderData: Partial<Order>) {
      this.loading = true
      try {
        const response = await api.post('/v1/sales-orders', orderData)
        this.orders.unshift(response.data)
        showSuccessMessage('Order created successfully')
        return response.data
      } catch (error) {
        showErrorMessage('Failed to create order')
        throw error
      } finally {
        this.loading = false
      }
    },

    async updateOrder(id: number, orderData: Partial<Order>) {
      this.loading = true
      try {
        const response = await api.put(`/v1/sales-orders/${id}`, orderData)
        const index = this.orders.findIndex(order => order.id === id)
        if (index !== -1) {
          this.orders[index] = response.data
        }
        showSuccessMessage('Order updated successfully')
        return response.data
      } catch (error) {
        showErrorMessage('Failed to update order')
        throw error
      } finally {
        this.loading = false
      }
    },

    async deleteOrder(id: number) {
      this.loading = true
      try {
        await api.delete(`/v1/sales-orders/${id}`)
        this.orders = this.orders.filter(order => order.id !== id)
        showSuccessMessage('Order deleted successfully')
      } catch (error) {
        showErrorMessage('Failed to delete order')
        throw error
      } finally {
        this.loading = false
      }
    },

    async convertToInvoice(id: number, invoiceData: any = {}) {
      this.loading = true
      try {
        const response = await api.post(`/v1/sales-orders/${id}/convert-to-invoice`, invoiceData)
        showSuccessMessage('Order converted to invoice successfully')
        return response.data
      } catch (error) {
        showErrorMessage('Failed to convert order to invoice')
        throw error
      } finally {
        this.loading = false
      }
    },

    async updateOrderStatus(id: number, status: string, notes?: string) {
      this.loading = true
      try {
        const response = await api.patch(`/v1/sales-orders/${id}/status`, {
          status,
          notes
        })
        const index = this.orders.findIndex(order => order.id === id)
        if (index !== -1) {
          this.orders[index] = response.data
        }
        showSuccessMessage('Order status updated successfully')
        return response.data
      } catch (error) {
        showErrorMessage('Failed to update order status')
        throw error
      } finally {
        this.loading = false
      }
    },

    async exportOrders(filters: OrderFilters = {}) {
      try {
        const response = await api.get('/v1/sales-orders-csv', { 
          params: filters,
          responseType: 'blob'
        })
        
        // Create download link
        const url = window.URL.createObjectURL(new Blob([response.data]))
        const link = document.createElement('a')
        link.href = url
        link.setAttribute('download', `sales-orders-${new Date().toISOString().split('T')[0]}.csv`)
        document.body.appendChild(link)
        link.click()
        link.remove()
        window.URL.revokeObjectURL(url)
        
        showSuccessMessage('Orders exported successfully')
      } catch (error) {
        showErrorMessage('Failed to export orders')
        throw error
      }
    },

    async getOrderStatistics(filters: OrderFilters = {}) {
      try {
        const response = await api.get('/v1/sales-orders/statistics', { params: filters })
        return response.data
      } catch (error) {
        showErrorMessage('Failed to fetch order statistics')
        throw error
      }
    },

    clearOrders() {
      this.orders = []
      this.orderData = {
        current_page: 1,
        last_page: 1,
        per_page: 25,
        total: 0,
        from: 0,
        to: 0
      }
    },

    setCurrentOrder(order: Order | null) {
      this.currentOrder = order
    }
  }
})