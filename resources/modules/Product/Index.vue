<template>
  <div class="card card-flush">
    <div class="card-header border-0 pt-6">
      <div class="card-title">
        <div class="d-flex align-items-center position-relative my-1">
          <span class="svg-icon svg-icon-1 position-absolute ms-4">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
              <rect opacity="0.5" x="17.0365" y="15.1223" width="8.15546" height="2" rx="1" transform="rotate(45 17.0365 15.1223)" fill="currentColor"/>
              <path d="M11 19C6.55556 19 3 15.4444 3 11C3 6.55556 6.55556 3 11 3C15.4444 3 19 6.55556 19 11C19 15.4444 15.4444 19 11 19ZM11 5C7.53333 5 5 7.53333 5 11C5 14.4667 7.53333 17 11 17C14.4667 17 17 14.4667 17 11C17 7.53333 14.4667 5 11 5Z" fill="currentColor"/>
            </svg>
          </span>
          <input
            type="text"
            v-model="search"
            @input="debouncedSearch"
            class="form-control form-control-solid w-250px ps-15"
            placeholder="Search Products..."
          />
        </div>
      </div>
      <div class="card-toolbar">
        <div class="d-flex justify-content-end flex-wrap my-1">
          <div class="me-2">
            <select
              v-model="filters.type"
              @change="fetchProducts"
              class="form-select form-select-solid"
            >
              <option value="">All Types</option>
              <option value="product">Products</option>
              <option value="service">Services</option>
            </select>
          </div>
          <div class="me-2">
            <select
              v-model="filters.warehouse_id"
              @change="onWarehouseChange"
              class="form-select form-select-solid"
            >
              <option value="">All Warehouses</option>
              <option v-for="warehouse in warehouses" :key="warehouse.value" :value="warehouse.value">
                {{ warehouse.label }}
              </option>
            </select>
          </div>
          <div class="me-2">
            <select
              v-model="filters.rack_id"
              @change="fetchProducts"
              class="form-select form-select-solid"
              :disabled="!filters.warehouse_id"
            >
              <option value="">All Racks</option>
              <option v-for="rack in racks" :key="rack.value" :value="rack.value">
                {{ rack.label }}
              </option>
            </select>
          </div>
          <router-link
            to="/products/create"
            class="btn btn-primary"
          >
            <span class="svg-icon svg-icon-2">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                <rect opacity="0.3" x="2" y="2" width="20" height="20" rx="5" fill="currentColor"/>
                <rect x="10.8891" y="17.8033" width="12" height="2" rx="1" transform="rotate(-90 10.8891 17.8033)" fill="currentColor"/>
                <rect x="17.3033" y="10.8891" width="12" height="2" rx="1" transform="rotate(-90 17.3033 10.8891)" fill="currentColor"/>
              </svg>
            </span>
            Add Product
          </router-link>
        </div>
      </div>
    </div>

    <div class="card-body pt-0">
      <div class="dataTables_wrapper dt-bootstrap4 no-footer">
        <div class="table-responsive">
          <table class="table align-middle table-row-dashed fs-6 gy-5 dataTable no-footer">
            <thead>
              <tr class="text-start text-muted fw-bolder fs-7 text-uppercase gs-0">
                <th>Code</th>
                <th>Name</th>
                <th>Type</th>
                <th>Category</th>
                <th>Warehouse</th>
                <th>Rack</th>
                <th>HSN Code</th>
                <th>Batch Number</th>
                <th>Supplier</th>
                <th>Status</th>
                <th class="text-end">Actions</th>
              </tr>
            </thead>
            <tbody class="fw-semibold text-gray-600">
              <tr v-if="loading">
                <td colspan="11" class="text-center">
                  <div class="spinner-border spinner-border-lg align-middle ms-2" role="status"></div>
                </td>
              </tr>
              <tr v-else-if="products.data.length === 0">
                <td colspan="11" class="text-center">No products found</td>
              </tr>
              <tr v-for="product in products.data" :key="product.id">
                <td>{{ product.code }}</td>
                <td>
                  <div class="d-flex align-items-center">
                    <div class="d-flex justify-content-start flex-column">
                      <span class="text-gray-800 fw-bold text-hover-primary mb-1">{{ product.name }}</span>
                      <span class="text-muted fw-semibold text-muted d-block fs-7">{{ product.sku }}</span>
                    </div>
                  </div>
                </td>
                <td>
                  <span :class="getProductTypeClass(product.type)">
                    {{ product.type_label }}
                  </span>
                </td>
                <td>{{ product.category?.name || '-' }}</td>
                <td>{{ product.warehouse?.full_name || '-' }}</td>
                <td>{{ product.rack?.full_name || '-' }}</td>
                <td>{{ product.hsn_code || '-' }}</td>
                <td>{{ product.batch_number || '-' }}</td>
                <td>{{ product.supplier?.name || '-' }}</td>
                <td>
                  <span :class="product.active ? 'badge badge-success' : 'badge badge-danger'">
                    {{ product.active ? 'Active' : 'Inactive' }}
                  </span>
                </td>
                <td class="text-end">
                  <div class="d-flex justify-content-end flex-shrink-0">
                    <router-link
                      :to="`/products/${product.id}`"
                      class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1"
                    >
                      <span class="svg-icon svg-icon-3">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                          <path d="M15.5 12C15.5 10.6193 14.3807 9.5 13 9.5C11.6193 9.5 10.5 10.6193 10.5 12C10.5 13.3807 11.6193 14.5 13 14.5C14.3807 14.5 15.5 13.3807 15.5 12Z" fill="currentColor"/>
                          <path d="M12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2ZM4 12C4 7.58172 7.58172 4 12 4C16.4183 4 20 7.58172 20 12C20 16.4183 16.4183 20 12 20C7.58172 20 4 16.4183 4 12Z" fill="currentColor"/>
                        </svg>
                      </span>
                    </router-link>
                    <router-link
                      :to="`/products/${product.id}/edit`"
                      class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1"
                    >
                      <span class="svg-icon svg-icon-3">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                          <path opacity="0.3" d="M21.4 8.35303L19.241 10.511L13.485 4.755L15.643 2.59503C16.0248 2.21423 16.5426 1.99988 17.0825 1.99988C17.6224 1.99988 18.1402 2.21423 18.522 2.59503L21.4 5.47403C21.7808 5.85583 21.9952 6.37365 21.9952 6.91353C21.9952 7.45341 21.7808 7.97123 21.4 8.35303ZM3.68699 21.932L9.88699 19.865L4.13099 14.109L2.06399 20.309C1.98815 20.5354 1.97503 20.7787 2.02616 21.0121C2.07729 21.2456 2.19076 21.4605 2.35499 21.634C2.51918 21.8076 2.72779 21.9335 2.95833 21.9983C3.18888 22.0631 3.43345 22.0646 3.66499 22.0027L3.68699 21.932Z" fill="currentColor"/>
                          <path d="M5.574 21.3L3.692 21.928C3.46591 22.0037 3.22334 22.0171 2.99044 21.9664C2.75754 21.9156 2.54322 21.8029 2.36982 21.6396C2.19642 21.4763 2.07078 21.2688 2.00583 21.0394C1.94089 20.81 1.93917 20.5665 2.00092 20.3362L2.62892 18.454L5.574 21.3ZM4.13499 14.105L9.88599 19.856L19.242 10.5L13.491 4.74903L4.13499 14.105Z" fill="currentColor"/>
                        </svg>
                      </span>
                    </router-link>
                    <button
                      @click="deleteProduct(product)"
                      class="btn btn-icon btn-bg-light btn-active-color-danger btn-sm"
                    >
                      <span class="svg-icon svg-icon-3">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                          <path d="M5 9C5 8.44772 5.44772 8 6 8H18C18.5523 8 19 8.44772 19 9V18C19 19.6569 17.6569 21 16 21H8C6.34315 21 5 19.6569 5 18V9Z" fill="currentColor"/>
                          <path opacity="0.5" d="M5 5C5 4.44772 5.44772 4 6 4H18C18.5523 4 19 4.44772 19 5V5C19 5.55228 18.5523 6 18 6H6C5.44772 6 5 5.55228 5 5V5Z" fill="currentColor"/>
                          <path opacity="0.5" d="M9 3C9 2.44772 9.44772 2 10 2H14C14.5523 2 15 2.44772 15 3V3C15 3.55228 14.5523 4 14 4H10C9.44772 4 9 3.55228 9 3V3Z" fill="currentColor"/>
                        </svg>
                      </span>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="row">
          <div class="col-sm-12 col-md-5 d-flex align-items-center justify-content-center justify-content-md-start">
            <div class="dataTables_length">
              <label>
                <select
                  v-model="filters.per_page"
                  @change="fetchProducts"
                  class="form-select form-select-sm"
                >
                  <option value="10">10</option>
                  <option value="15">15</option>
                  <option value="25">25</option>
                  <option value="50">50</option>
                  <option value="100">100</option>
                </select>
              </label>
            </div>
          </div>
          <div class="col-sm-12 col-md-7 d-flex align-items-center justify-content-center justify-content-md-end">
            <div class="dataTables_paginate paging_simple_numbers">
              <ul class="pagination">
                <li class="paginate_button page-item previous" :class="{ disabled: products.current_page === 1 }">
                  <button @click="changePage(products.current_page - 1)" class="page-link">
                    <i class="previous"></i>
                  </button>
                </li>
                <li
                  v-for="page in getPageNumbers()"
                  :key="page"
                  class="paginate_button page-item"
                  :class="{ active: page === products.current_page }"
                >
                  <button @click="changePage(page)" class="page-link">{{ page }}</button>
                </li>
                <li class="paginate_button page-item next" :class="{ disabled: products.current_page === products.last_page }">
                  <button @click="changePage(products.current_page + 1)" class="page-link">
                    <i class="next"></i>
                  </button>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, reactive, onMounted, computed } from 'vue'
import { debounce } from 'lodash'
import Swal from 'sweetalert2'
import api from '@/services/api'

export default {
  name: 'ProductIndex',
  setup() {
    const loading = ref(false)
    const search = ref('')
    const products = ref({ data: [] })
    const warehouses = ref([])
    const racks = ref([])

    const filters = reactive({
      per_page: 15,
      type: '',
      warehouse_id: '',
      rack_id: '',
      search: ''
    })

    const debouncedSearch = debounce(() => {
      filters.search = search.value
      fetchProducts()
    }, 300)

    const fetchProducts = async () => {
      loading.value = true
      try {
        const response = await api.get('/api/products', { params: filters })
        products.value = response.data
      } catch (error) {
        console.error('Error fetching products:', error)
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'Failed to fetch products'
        })
      } finally {
        loading.value = false
      }
    }

    const fetchWarehouses = async () => {
      try {
        const response = await api.get('/api/products/warehouses')
        warehouses.value = response.data
      } catch (error) {
        console.error('Error fetching warehouses:', error)
      }
    }

    const fetchRacks = async (warehouseId = null) => {
      try {
        const params = warehouseId ? { warehouse_id: warehouseId } : {}
        const response = await api.get('/api/products/racks', { params })
        racks.value = response.data
      } catch (error) {
        console.error('Error fetching racks:', error)
      }
    }

    const onWarehouseChange = () => {
      filters.rack_id = ''
      fetchRacks(filters.warehouse_id)
      fetchProducts()
    }

    const changePage = (page) => {
      if (page >= 1 && page <= products.value.last_page) {
        filters.page = page
        fetchProducts()
      }
    }

    const getPageNumbers = () => {
      const current = products.value.current_page
      const last = products.value.last_page
      const delta = 2
      const range = []
      const rangeWithDots = []

      for (let i = Math.max(2, current - delta); i <= Math.min(last - 1, current + delta); i++) {
        range.push(i)
      }

      if (current - delta > 2) {
        rangeWithDots.push(1, '...')
      } else {
        rangeWithDots.push(1)
      }

      rangeWithDots.push(...range)

      if (current + delta < last - 1) {
        rangeWithDots.push('...', last)
      } else {
        rangeWithDots.push(last)
      }

      return rangeWithDots
    }

    const getProductTypeClass = (type) => {
      const classes = {
        product: 'badge badge-primary',
        service: 'badge badge-info'
      }
      return classes[type] || 'badge badge-secondary'
    }

    const deleteProduct = async (product) => {
      const result = await Swal.fire({
        title: 'Are you sure?',
        text: `You are about to delete "${product.name}". This action cannot be undone.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!'
      })

      if (result.isConfirmed) {
        try {
          await api.delete(`/api/products/${product.id}`)
          await fetchProducts()
          Swal.fire({
            icon: 'success',
            title: 'Deleted!',
            text: 'Product has been deleted.'
          })
        } catch (error) {
          console.error('Error deleting product:', error)
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Failed to delete product'
          })
        }
      }
    }

    onMounted(() => {
      fetchProducts()
      fetchWarehouses()
      fetchRacks()
    })

    return {
      loading,
      search,
      products,
      warehouses,
      racks,
      filters,
      debouncedSearch,
      fetchProducts,
      onWarehouseChange,
      changePage,
      getPageNumbers,
      getProductTypeClass,
      deleteProduct
    }
  }
}
</script>