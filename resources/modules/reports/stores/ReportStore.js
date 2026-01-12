import { defineStore } from 'pinia'
import { api } from '@/services/api'
import { showSuccessMessage, showErrorMessage } from '@/utility/NotificationUtils'

export interface DashboardData {
  sales_overview: {
    total_sales: number
    total_revenue: number
    average_sale_value: number
  }
  order_statistics: {
    total_orders: number
    total_amount: number
    pending_orders: number
    confirmed_orders: number
    converted_orders: number
    cancelled_orders: number
    average_order_value: number
  }
  commission_summary: {
    total_commissions: number
    total_commission_amount: number
    pending_commissions: number
    pending_amount: number
    approved_commissions: number
    approved_amount: number
    paid_commissions: number
    paid_amount: number
  }
  top_products: any[]
  recent_orders: any[]
}

export interface SalesChartData {
  month: string
  total: number
}

export interface ReportFilters {
  start_date?: string
  end_date?: string
  company_id?: number
  f_date_range?: string
  f_company_id?: string
}

export const useReportStore = defineStore('reports', {
  state: () => ({
    dashboardData: {} as DashboardData,
    salesChartData: [] as SalesChartData[],
    profitLossData: null as any,
    stockSummaryData: null as any,
    salesByProductData: null as any,
    commissionSummaryData: null as any,
    loading: false,
    chartLoading: false
  }),

  getters: {
    totalRevenue: (state) => state.dashboardData.sales_overview?.total_revenue || 0,
    totalOrders: (state) => state.dashboardData.order_statistics?.total_orders || 0,
    totalCommissions: (state) => state.dashboardData.commission_summary?.total_commission_amount || 0,
    
    orderStatusBreakdown: (state) => {
      const stats = state.dashboardData.order_statistics
      if (!stats) return []
      
      return [
        { status: 'pending', count: stats.pending_orders || 0 },
        { status: 'confirmed', count: stats.confirmed_orders || 0 },
        { status: 'converted', count: stats.converted_orders || 0 },
        { status: 'cancelled', count: stats.cancelled_orders || 0 }
      ]
    }
  },

  actions: {
    async fetchDashboardData(filters: ReportFilters = {}) {
      this.loading = true
      try {
        const response = await api.get('/v1/reports/dashboard', { params: filters })
        this.dashboardData = response.data
        return response.data
      } catch (error) {
        showErrorMessage('Failed to fetch dashboard data')
        throw error
      } finally {
        this.loading = false
      }
    },

    async fetchSalesByMonth(filters: ReportFilters = {}) {
      this.chartLoading = true
      try {
        const response = await api.get('/v1/reports/sales/by-month', { params: filters })
        this.salesChartData = response.data.data || []
        return response.data
      } catch (error) {
        showErrorMessage('Failed to fetch sales data')
        throw error
      } finally {
        this.chartLoading = false
      }
    },

    async fetchProfitLoss(filters: ReportFilters = {}) {
      this.loading = true
      try {
        const response = await api.get('/v1/reports/profit-loss', { params: filters })
        this.profitLossData = response.data
        return response.data
      } catch (error) {
        showErrorMessage('Failed to fetch profit & loss data')
        throw error
      } finally {
        this.loading = false
      }
    },

    async fetchStockSummary(filters: ReportFilters = {}) {
      this.loading = true
      try {
        const response = await api.get('/v1/reports/stock/summary', { params: filters })
        this.stockSummaryData = response.data
        return response.data
      } catch (error) {
        showErrorMessage('Failed to fetch stock summary')
        throw error
      } finally {
        this.loading = false
      }
    },

    async fetchSalesByProduct(filters: ReportFilters = {}) {
      this.loading = true
      try {
        const response = await api.get('/v1/reports/sales/by-product', { params: filters })
        this.salesByProductData = response.data
        return response.data
      } catch (error) {
        showErrorMessage('Failed to fetch sales by product data')
        throw error
      } finally {
        this.loading = false
      }
    },

    async fetchCommissionSummary(filters: ReportFilters = {}) {
      this.loading = true
      try {
        const response = await api.get('/v1/reports/commissions/summary', { params: filters })
        this.commissionSummaryData = response.data
        return response.data
      } catch (error) {
        showErrorMessage('Failed to fetch commission summary')
        throw error
      } finally {
        this.loading = false
      }
    },

    async generateProfitLossReport(filters: ReportFilters = {}) {
      try {
        const response = await api.get('/v1/reports/pdf/profit-loss', {
          params: filters,
          responseType: 'blob'
        })
        
        // Create download link
        const url = window.URL.createObjectURL(new Blob([response.data]))
        const link = document.createElement('a')
        link.href = url
        link.setAttribute('download', `profit-loss-${new Date().toISOString().split('T')[0]}.pdf`)
        document.body.appendChild(link)
        link.click()
        link.remove()
        window.URL.revokeObjectURL(url)
        
        showSuccessMessage('Profit & Loss report generated successfully')
      } catch (error) {
        showErrorMessage('Failed to generate Profit & Loss report')
        throw error
      }
    },

    async generateSalesByProductReport(filters: ReportFilters = {}) {
      try {
        const response = await api.get('/v1/reports/csv/sales-product', {
          params: filters,
          responseType: 'blob'
        })
        
        // Create download link
        const url = window.URL.createObjectURL(new Blob([response.data]))
        const link = document.createElement('a')
        link.href = url
        link.setAttribute('download', `sales-by-product-${new Date().toISOString().split('T')[0]}.csv`)
        document.body.appendChild(link)
        link.click()
        link.remove()
        window.URL.revokeObjectURL(url)
        
        showSuccessMessage('Sales by Product report generated successfully')
      } catch (error) {
        showErrorMessage('Failed to generate Sales by Product report')
        throw error
      }
    },

    async generateCommissionSummaryReport(filters: ReportFilters = {}) {
      try {
        const response = await api.get('/v1/reports/csv/sales-commission', {
          params: filters,
          responseType: 'blob'
        })
        
        // Create download link
        const url = window.URL.createObjectURL(new Blob([response.data]))
        const link = document.createElement('a')
        link.href = url
        link.setAttribute('download', `commission-summary-${new Date().toISOString().split('T')[0]}.csv`)
        document.body.appendChild(link)
        link.click()
        link.remove()
        window.URL.revokeObjectURL(url)
        
        showSuccessMessage('Commission Summary report generated successfully')
      } catch (error) {
        showErrorMessage('Failed to generate Commission Summary report')
        throw error
      }
    },

    async generateStockSummaryReport(filters: ReportFilters = {}) {
      try {
        const response = await api.get('/v1/reports/csv/summary-stock', {
          params: filters,
          responseType: 'blob'
        })
        
        // Create download link
        const url = window.URL.createObjectURL(new Blob([response.data]))
        const link = document.createElement('a')
        link.href = url
        link.setAttribute('download', `stock-summary-${new Date().toISOString().split('T')[0]}.csv`)
        document.body.appendChild(link)
        link.click()
        link.remove()
        window.URL.revokeObjectURL(url)
        
        showSuccessMessage('Stock Summary report generated successfully')
      } catch (error) {
        showErrorMessage('Failed to generate Stock Summary report')
        throw error
      }
    },

    clearReportData() {
      this.dashboardData = {} as DashboardData
      this.salesChartData = []
      this.profitLossData = null
      this.stockSummaryData = null
      this.salesByProductData = null
      this.commissionSummaryData = null
    }
  }
})