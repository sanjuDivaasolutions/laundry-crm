<template>
  <div class="card card-flush">
    <div class="card-header">
      <div class="card-title">
        <h3 class="fw-bold">Product Details</h3>
      </div>
      <div class="card-toolbar">
        <router-link to="/products" class="btn btn-light me-2">
          <span class="svg-icon svg-icon-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
              <rect opacity="0.5" x="6" y="17.3137" width="16" height="2" rx="1" transform="rotate(-45 6 17.3137)" fill="currentColor"/>
              <rect opacity="0.5" x="7" y="6.3137" width="16" height="2" rx="1" transform="rotate(45 7 6.3137)" fill="currentColor"/>
            </svg>
          </span>
          Back
        </router-link>
        <router-link
          :to="`/products/${product.id}/edit`"
          class="btn btn-primary"
        >
          <span class="svg-icon svg-icon-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
              <path opacity="0.3" d="M21.4 8.35303L19.241 10.511L13.485 4.755L15.643 2.59503C16.0248 2.21423 16.5426 1.99988 17.0825 1.99988C17.6224 1.99988 18.1402 2.21423 18.522 2.59503L21.4 5.47403C21.7808 5.85583 21.9952 6.37365 21.9952 6.91353C21.9952 7.45341 21.7808 7.97123 21.4 8.35303ZM3.68699 21.932L9.88699 19.865L4.13099 14.109L2.06399 20.309C1.98815 20.5354 1.97503 20.7787 2.02616 21.0121C2.07729 21.2456 2.19076 21.4605 2.35499 21.634C2.51918 21.8076 2.72779 21.9335 2.95833 21.9983C3.18888 22.0631 3.43345 22.0646 3.66499 22.0027L3.68699 21.932Z" fill="currentColor"/>
              <path d="M5.574 21.3L3.692 21.928C3.46591 22.0037 3.22334 22.0171 2.99044 21.9664C2.75754 21.9156 2.54322 21.8029 2.36982 21.6396C2.19642 21.4763 2.07078 21.2688 2.00583 21.0394C1.94089 20.81 1.93917 20.5665 2.00092 20.3362L2.62892 18.454L5.574 21.3ZM4.13499 14.105L9.88599 19.856L19.242 10.5L13.491 4.74903L4.13499 14.105Z" fill="currentColor"/>
            </svg>
          </span>
          Edit
        </router-link>
      </div>
    </div>

    <div class="card-body" v-if="loading">
      <div class="d-flex justify-content-center p-10">
        <div class="spinner-border spinner-border-lg align-middle" role="status"></div>
      </div>
    </div>

    <div class="card-body" v-else-if="product">
      <div class="row">
        <div class="col-md-6">
          <div class="mb-7">
            <label class="fs-6 fw-bold text-gray-700">Product Code</label>
            <div class="fs-6 text-gray-800">{{ product.code }}</div>
          </div>

          <div class="mb-7">
            <label class="fs-6 fw-bold text-gray-700">Product Name</label>
            <div class="fs-6 text-gray-800">{{ product.name }}</div>
          </div>

          <div class="mb-7">
            <label class="fs-6 fw-bold text-gray-700">Type</label>
            <div>
              <span :class="getProductTypeClass(product.type)">
                {{ product.type_label }}
              </span>
            </div>
          </div>

          <div class="mb-7">
            <label class="fs-6 fw-bold text-gray-700">SKU</label>
            <div class="fs-6 text-gray-800">{{ product.sku || '-' }}</div>
          </div>

          <div class="mb-7">
            <label class="fs-6 fw-bold text-gray-700">Barcode</label>
            <div class="fs-6 text-gray-800">{{ product.barcode || '-' }}</div>
          </div>

          <div class="mb-7">
            <label class="fs-6 fw-bold text-gray-700">Category</label>
            <div class="fs-6 text-gray-800">{{ product.category?.name || '-' }}</div>
          </div>

          <div class="mb-7">
            <label class="fs-6 fw-bold text-gray-700">Supplier</label>
            <div class="fs-6 text-gray-800">{{ product.supplier?.name || '-' }}</div>
          </div>
        </div>

        <div class="col-md-6">
          <!-- Product-specific fields -->
          <template v-if="product.type === 'product'">
            <div class="mb-7">
              <label class="fs-6 fw-bold text-gray-700">HSN Code</label>
              <div class="fs-6 text-gray-800">{{ product.hsn_code || '-' }}</div>
            </div>

            <div class="mb-7">
              <label class="fs-6 fw-bold text-gray-700">Batch Number</label>
              <div class="fs-6 text-gray-800">{{ product.batch_number || '-' }}</div>
            </div>

            <div class="mb-7">
              <label class="fs-6 fw-bold text-gray-700">Warehouse</label>
              <div class="fs-6 text-gray-800">{{ product.warehouse?.full_name || '-' }}</div>
            </div>

            <div class="mb-7">
              <label class="fs-6 fw-bold text-gray-700">Rack</label>
              <div class="fs-6 text-gray-800">{{ product.rack?.full_name || '-' }}</div>
            </div>

            <div class="mb-7">
              <label class="fs-6 fw-bold text-gray-700">Full Location</label>
              <div class="fs-6 text-gray-800">{{ product.rack?.full_location || '-' }}</div>
            </div>
          </template>

          <div class="mb-7">
            <label class="fs-6 fw-bold text-gray-700">Manufacturer</label>
            <div class="fs-6 text-gray-800">{{ product.manufacturer || '-' }}</div>
          </div>

          <div class="mb-7">
            <label class="fs-6 fw-bold text-gray-700">Unit 1</label>
            <div class="fs-6 text-gray-800">{{ product.unit_01?.name || '-' }}</div>
          </div>

          <div class="mb-7">
            <label class="fs-6 fw-bold text-gray-700">Unit 2</label>
            <div class="fs-6 text-gray-800">{{ product.unit_02?.name || '-' }}</div>
          </div>

          <div class="mb-7">
            <label class="fs-6 fw-bold text-gray-700">Status</label>
            <div>
              <span :class="product.active ? 'badge badge-success' : 'badge badge-danger'">
                {{ product.active ? 'Active' : 'Inactive' }}
              </span>
            </div>
          </div>

          <div class="mb-7">
            <label class="fs-6 fw-bold text-gray-700">Returnable</label>
            <div>
              <span :class="product.is_returnable ? 'badge badge-success' : 'badge badge-secondary'">
                {{ product.is_returnable ? 'Yes' : 'No' }}
              </span>
            </div>
          </div>

          <div class="mb-7">
            <label class="fs-6 fw-bold text-gray-700">Has Inventory</label>
            <div>
              <span :class="product.has_inventory ? 'badge badge-success' : 'badge badge-secondary'">
                {{ product.has_inventory ? 'Yes' : 'No' }}
              </span>
            </div>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-12">
          <div class="mb-7">
            <label class="fs-6 fw-bold text-gray-700">Description</label>
            <div class="fs-6 text-gray-800">{{ product.description || '-' }}</div>
          </div>
        </div>
      </div>

      <!-- Additional Information Tabs -->
      <div class="row mt-10">
        <div class="col-12">
          <ul class="nav nav-tabs nav-line-tabs mb-5 fs-6">
            <li class="nav-item">
              <a class="nav-link active" data-bs-toggle="tab" href="#kt_tab_pane_1">Features</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" data-bs-toggle="tab" href="#kt_tab_pane_2">Prices</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" data-bs-toggle="tab" href="#kt_tab_pane_3">Stock</a>
            </li>
          </ul>

          <div class="tab-content" id="myTabContent">
            <div class="tab-pane fade show active" id="kt_tab_pane_1" role="tabpanel">
              <div v-if="product.features && product.features.length > 0">
                <div class="table-responsive">
                  <table class="table align-middle table-row-dashed fs-6 gy-5">
                    <thead>
                      <tr class="text-start text-muted fw-bolder fs-7 text-uppercase gs-0">
                        <th>Feature</th>
                        <th>Value</th>
                        <th>Description</th>
                      </tr>
                    </thead>
                    <tbody class="fw-semibold text-gray-600">
                      <tr v-for="feature in product.features" :key="feature.id">
                        <td>{{ feature.name }}</td>
                        <td>{{ feature.value }}</td>
                        <td>{{ feature.description || '-' }}</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
              <div v-else class="text-center py-10">
                <div class="text-gray-400">No features found</div>
              </div>
            </div>

            <div class="tab-pane fade" id="kt_tab_pane_2" role="tabpanel">
              <div v-if="product.prices && product.prices.length > 0">
                <div class="table-responsive">
                  <table class="table align-middle table-row-dashed fs-6 gy-5">
                    <thead>
                      <tr class="text-start text-muted fw-bolder fs-7 text-uppercase gs-0">
                        <th>Price</th>
                        <th>Currency</th>
                        <th>Valid From</th>
                        <th>Valid To</th>
                      </tr>
                    </thead>
                    <tbody class="fw-semibold text-gray-600">
                      <tr v-for="price in product.prices" :key="price.id">
                        <td>{{ price.price }}</td>
                        <td>{{ price.currency }}</td>
                        <td>{{ price.valid_from || '-' }}</td>
                        <td>{{ price.valid_to || '-' }}</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
              <div v-else class="text-center py-10">
                <div class="text-gray-400">No prices found</div>
              </div>
            </div>

            <div class="tab-pane fade" id="kt_tab_pane_3" role="tabpanel">
              <div v-if="product.stock && product.stock.length > 0">
                <div class="table-responsive">
                  <table class="table align-middle table-row-dashed fs-6 gy-5">
                    <thead>
                      <tr class="text-start text-muted fw-bolder fs-7 text-uppercase gs-0">
                        <th>Warehouse</th>
                        <th>Rack</th>
                        <th>Quantity</th>
                        <th>Reserved</th>
                        <th>Available</th>
                      </tr>
                    </thead>
                    <tbody class="fw-semibold text-gray-600">
                      <tr v-for="stock in product.stock" :key="stock.id">
                        <td>{{ stock.warehouse?.name || '-' }}</td>
                        <td>{{ stock.rack?.name || '-' }}</td>
                        <td>{{ stock.quantity }}</td>
                        <td>{{ stock.reserved || 0 }}</td>
                        <td>{{ (stock.quantity || 0) - (stock.reserved || 0) }}</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
              <div v-else class="text-center py-10">
                <div class="text-gray-400">No stock information found</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="card-body" v-else>
      <div class="text-center py-10">
        <div class="text-gray-400">Product not found</div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import api from '@/services/api'

export default {
  name: 'ProductShow',
  setup() {
    const route = useRoute()
    const loading = ref(false)
    const product = ref(null)

    const fetchProduct = async (id) => {
      loading.value = true
      try {
        const response = await api.get(`/api/products/${id}`)
        product.value = response.data
      } catch (error) {
        console.error('Error fetching product:', error)
        product.value = null
      } finally {
        loading.value = false
      }
    }

    const getProductTypeClass = (type) => {
      const classes = {
        product: 'badge badge-primary',
        service: 'badge badge-info'
      }
      return classes[type] || 'badge badge-secondary'
    }

    onMounted(() => {
      fetchProduct(route.params.id)
    })

    return {
      loading,
      product,
      getProductTypeClass
    }
  }
}
</script>