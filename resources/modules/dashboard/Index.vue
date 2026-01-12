<template>
  <div class="dashboard-page">
    <div class="page-header d-print-none">
      <div class="container-xl">
        <div class="row g-2 align-items-center">
          <div class="col">
            <h2 class="page-title">Dashboard</h2>
          </div>
        </div>
      </div>
    </div>

    <div class="page-body">
      <div class="container-xl">
        <div class="row row-deck row-cards">
          <!-- Today's Sales Card -->
          <div class="col-sm-6 col-lg-3">
            <div class="card">
              <div class="card-body">
                <div class="d-flex align-items-center">
                  <div class="subheader">Today's Sales</div>
                  <div class="ms-auto lh-1">
                    <div class="dropdown">
                      <a class="dropdown-toggle text-muted" href="#" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Last 7 days</a>
                    </div>
                  </div>
                </div>
                <div class="h1 mb-3">{{ formatCurrency(dashboardData.today_sales) }}</div>
                <div class="d-flex mb-2">
                  <div>Total sales for today</div>
                </div>
              </div>
            </div>
          </div>

          <!-- Today's Purchase Card -->
          <div class="col-sm-6 col-lg-3">
            <div class="card">
              <div class="card-body">
                <div class="d-flex align-items-center">
                  <div class="subheader">Today's Purchase</div>
                  <div class="ms-auto lh-1">
                    <div class="dropdown">
                      <a class="dropdown-toggle text-muted" href="#" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Last 7 days</a>
                    </div>
                  </div>
                </div>
                <div class="h1 mb-3">{{ formatCurrency(dashboardData.today_purchase) }}</div>
                <div class="d-flex mb-2">
                  <div>Total purchase for today</div>
                </div>
              </div>
            </div>
          </div>

          <!-- Today's Sales Invoices Card -->
          <div class="col-sm-6 col-lg-3">
            <div class="card">
              <div class="card-body">
                <div class="d-flex align-items-center">
                  <div class="subheader">Today's Sales Invoices</div>
                  <div class="ms-auto lh-1">
                    <div class="dropdown">
                      <a class="dropdown-toggle text-muted" href="#" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Last 7 days</a>
                    </div>
                  </div>
                </div>
                <div class="h1 mb-3">{{ dashboardData.today_sales_invoices.count }}</div>
                <div class="d-flex mb-2">
                  <div>Total: {{ formatCurrency(dashboardData.today_sales_invoices.total_amount) }}</div>
                </div>
              </div>
            </div>
          </div>

          <!-- Today's Purchase Invoices Card -->
          <div class="col-sm-6 col-lg-3">
            <div class="card">
              <div class="card-body">
                <div class="d-flex align-items-center">
                  <div class="subheader">Today's Purchase Invoices</div>
                  <div class="ms-auto lh-1">
                    <div class="dropdown">
                      <a class="dropdown-toggle text-muted" href="#" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Last 7 days</a>
                    </div>
                  </div>
                </div>
                <div class="h1 mb-3">{{ dashboardData.today_purchase_invoices.count }}</div>
                <div class="d-flex mb-2">
                  <div>Total: {{ formatCurrency(dashboardData.today_purchase_invoices.total_amount) }}</div>
                </div>
              </div>
            </div>
          </div>

          <!-- Low Stock Items Card -->
          <div class="col-sm-6 col-lg-3">
            <div class="card">
              <div class="card-body">
                <div class="d-flex align-items-center">
                  <div class="subheader">Low Stock Items</div>
                  <div class="ms-auto lh-1">
                    <div class="dropdown">
                      <a href="#" class="btn-action" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                          <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                          <path d="M12 12m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0"></path>
                          <path d="M12 12m-5 0a5 5 0 1 0 10 0a5 5 0 1 0 -10 0"></path>
                        </svg>
                      </a>
                      <div class="dropdown-menu dropdown-menu-end">
                        <a class="dropdown-item" href="/products">View All Products</a>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="h1 mb-3">{{ dashboardData.low_stock_items.length }}</div>
                <div class="d-flex mb-2">
                  <div>Items below reorder level</div>
                </div>
                <div v-if="dashboardData.low_stock_items.length > 0" class="mt-2">
                  <div v-for="item in dashboardData.low_stock_items.slice(0, 3)" :key="item.id" class="text-muted small">
                    {{ item.name }}: {{ item.current_stock }} {{ item.unit }}
                  </div>
                  <div v-if="dashboardData.low_stock_items.length > 3" class="text-muted small">
                    ...and {{ dashboardData.low_stock_items.length - 3 }} more
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Pending Quotations Card -->
          <div class="col-sm-6 col-lg-3">
            <div class="card">
              <div class="card-body">
                <div class="d-flex align-items-center">
                  <div class="subheader">Pending Quotations</div>
                  <div class="ms-auto lh-1">
                    <div class="dropdown">
                      <a href="#" class="btn-action" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                          <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                          <path d="M12 12m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0"></path>
                          <path d="M12 12m-5 0a5 5 0 1 0 10 0a5 5 0 1 0 -10 0"></path>
                        </svg>
                      </a>
                      <div class="dropdown-menu dropdown-menu-end">
                        <a class="dropdown-item" href="/quotations">View All Quotations</a>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="h1 mb-3">{{ dashboardData.pending_quotations }}</div>
                <div class="d-flex mb-2">
                  <div>Quotations awaiting approval</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useDashboardStore } from './DashboardStore'
import { formatCurrency } from '@utility@/currency'

const dashboardStore = useDashboardStore()
const dashboardData = ref({
  today_sales: 0,
  today_purchase: 0,
  today_sales_invoices: {
    count: 0,
    total_amount: 0
  },
  today_purchase_invoices: {
    count: 0,
    total_amount: 0
  },
  low_stock_items: [],
  pending_quotations: 0
})

const loadDashboardData = async () => {
  try {
    const response = await dashboardStore.fetchDashboardCards()
    dashboardData.value = response.data
  } catch (error) {
    console.error('Error loading dashboard data:', error)
  }
}

onMounted(() => {
  loadDashboardData()
})
</script>

<style scoped>
.dashboard-page {
  min-height: 100vh;
}

.card {
  transition: transform 0.2s ease-in-out;
}

.card:hover {
  transform: translateY(-2px);
}

.h1 {
  font-size: 2rem;
  font-weight: 600;
}

.subheader {
  font-size: 0.875rem;
  color: #6c757d;
  font-weight: 500;
}

.text-muted {
  color: #6c757d;
}

.small {
  font-size: 0.875rem;
}
</style>