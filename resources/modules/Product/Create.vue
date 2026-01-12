<template>
  <div class="card card-flush">
    <div class="card-header">
      <div class="card-title">
        <h3 class="fw-bold">{{ isEdit ? 'Edit Product' : 'Create Product' }}</h3>
      </div>
      <div class="card-toolbar">
        <router-link to="/products" class="btn btn-light">
          <span class="svg-icon svg-icon-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
              <rect opacity="0.5" x="6" y="17.3137" width="16" height="2" rx="1" transform="rotate(-45 6 17.3137)" fill="currentColor"/>
              <rect opacity="0.5" x="7" y="6.3137" width="16" height="2" rx="1" transform="rotate(45 7 6.3137)" fill="currentColor"/>
            </svg>
          </span>
          Back
        </router-link>
      </div>
    </div>

    <div class="card-body">
      <form @submit.prevent="submitForm" class="form">
        <div class="row mb-6">
          <label class="col-lg-4 col-form-label fw-bold fs-6">Product Type</label>
          <div class="col-lg-8 fv-row">
            <select
              v-model="form.type"
              @change="onTypeChange"
              class="form-select form-select-solid"
              :class="{ 'is-invalid': errors.type }"
            >
              <option value="">Select Type</option>
              <option value="product">Product</option>
              <option value="service">Service</option>
            </select>
            <div v-if="errors.type" class="invalid-feedback">{{ errors.type[0] }}</div>
          </div>
        </div>

        <div class="row mb-6">
          <label class="col-lg-4 col-form-label fw-bold fs-6">Code <span class="text-danger">*</span></label>
          <div class="col-lg-8 fv-row">
            <input
              v-model="form.code"
              type="text"
              class="form-control form-control-solid"
              placeholder="Product code"
              :class="{ 'is-invalid': errors.code }"
            />
            <div v-if="errors.code" class="invalid-feedback">{{ errors.code[0] }}</div>
          </div>
        </div>

        <div class="row mb-6">
          <label class="col-lg-4 col-form-label fw-bold fs-6">Name <span class="text-danger">*</span></label>
          <div class="col-lg-8 fv-row">
            <input
              v-model="form.name"
              type="text"
              class="form-control form-control-solid"
              placeholder="Product name"
              :class="{ 'is-invalid': errors.name }"
            />
            <div v-if="errors.name" class="invalid-feedback">{{ errors.name[0] }}</div>
          </div>
        </div>

        <div class="row mb-6">
          <label class="col-lg-4 col-form-label fw-bold fs-6">SKU</label>
          <div class="col-lg-8 fv-row">
            <input
              v-model="form.sku"
              type="text"
              class="form-control form-control-solid"
              placeholder="Stock Keeping Unit"
              :class="{ 'is-invalid': errors.sku }"
            />
            <div v-if="errors.sku" class="invalid-feedback">{{ errors.sku[0] }}</div>
          </div>
        </div>

        <div class="row mb-6">
          <label class="col-lg-4 col-form-label fw-bold fs-6">Barcode</label>
          <div class="col-lg-8 fv-row">
            <input
              v-model="form.barcode"
              type="text"
              class="form-control form-control-solid"
              placeholder="Barcode"
              :class="{ 'is-invalid': errors.barcode }"
            />
            <div v-if="errors.barcode" class="invalid-feedback">{{ errors.barcode[0] }}</div>
          </div>
        </div>

        <!-- Product-specific fields -->
        <template v-if="form.type === 'product'">
          <div class="row mb-6">
            <label class="col-lg-4 col-form-label fw-bold fs-6">HSN Code</label>
            <div class="col-lg-8 fv-row">
              <input
                v-model="form.hsn_code"
                type="text"
                class="form-control form-control-solid"
                placeholder="HSN/SAC Code"
                :class="{ 'is-invalid': errors.hsn_code }"
              />
              <div v-if="errors.hsn_code" class="invalid-feedback">{{ errors.hsn_code[0] }}</div>
            </div>
          </div>

          <div class="row mb-6">
            <label class="col-lg-4 col-form-label fw-bold fs-6">Batch Number</label>
            <div class="col-lg-8 fv-row">
              <input
                v-model="form.batch_number"
                type="text"
                class="form-control form-control-solid"
                placeholder="Batch Number"
                :class="{ 'is-invalid': errors.batch_number }"
              />
              <div v-if="errors.batch_number" class="invalid-feedback">{{ errors.batch_number[0] }}</div>
            </div>
          </div>

          <div class="row mb-6">
            <label class="col-lg-4 col-form-label fw-bold fs-6">Warehouse</label>
            <div class="col-lg-8 fv-row">
              <select
                v-model="form.warehouse_id"
                @change="onWarehouseChange"
                class="form-select form-select-solid"
                :class="{ 'is-invalid': errors.warehouse_id }"
              >
                <option value="">Select Warehouse</option>
                <option v-for="warehouse in warehouses" :key="warehouse.value" :value="warehouse.value">
                  {{ warehouse.label }}
                </option>
              </select>
              <div v-if="errors.warehouse_id" class="invalid-feedback">{{ errors.warehouse_id[0] }}</div>
            </div>
          </div>

          <div class="row mb-6">
            <label class="col-lg-4 col-form-label fw-bold fs-6">Rack</label>
            <div class="col-lg-8 fv-row">
              <select
                v-model="form.rack_id"
                class="form-select form-select-solid"
                :class="{ 'is-invalid': errors.rack_id }"
                :disabled="!form.warehouse_id"
              >
                <option value="">Select Rack</option>
                <option v-for="rack in racks" :key="rack.value" :value="rack.value">
                  {{ rack.label }}
                </option>
              </select>
              <div v-if="errors.rack_id" class="invalid-feedback">{{ errors.rack_id[0] }}</div>
            </div>
          </div>
        </template>

        <div class="row mb-6">
          <label class="col-lg-4 col-form-label fw-bold fs-6">Category</label>
          <div class="col-lg-8 fv-row">
            <select
              v-model="form.category_id"
              class="form-select form-select-solid"
              :class="{ 'is-invalid': errors.category_id }"
            >
              <option value="">Select Category</option>
              <option v-for="category in categories" :key="category.value" :value="category.value">
                {{ category.label }}
              </option>
            </select>
            <div v-if="errors.category_id" class="invalid-feedback">{{ errors.category_id[0] }}</div>
          </div>
        </div>

        <div class="row mb-6">
          <label class="col-lg-4 col-form-label fw-bold fs-6">Supplier</label>
          <div class="col-lg-8 fv-row">
            <select
              v-model="form.supplier_id"
              class="form-select form-select-solid"
              :class="{ 'is-invalid': errors.supplier_id }"
            >
              <option value="">Select Supplier</option>
              <option v-for="supplier in suppliers" :key="supplier.value" :value="supplier.value">
                {{ supplier.label }}
              </option>
            </select>
            <div v-if="errors.supplier_id" class="invalid-feedback">{{ errors.supplier_id[0] }}</div>
          </div>
        </div>

        <div class="row mb-6">
          <label class="col-lg-4 col-form-label fw-bold fs-6">Description</label>
          <div class="col-lg-8 fv-row">
            <textarea
              v-model="form.description"
              rows="3"
              class="form-control form-control-solid"
              placeholder="Product description"
              :class="{ 'is-invalid': errors.description }"
            ></textarea>
            <div v-if="errors.description" class="invalid-feedback">{{ errors.description[0] }}</div>
          </div>
        </div>

        <div class="row mb-6">
          <label class="col-lg-4 col-form-label fw-bold fs-6">Manufacturer</label>
          <div class="col-lg-8 fv-row">
            <input
              v-model="form.manufacturer"
              type="text"
              class="form-control form-control-solid"
              placeholder="Manufacturer"
              :class="{ 'is-invalid': errors.manufacturer }"
            />
            <div v-if="errors.manufacturer" class="invalid-feedback">{{ errors.manufacturer[0] }}</div>
          </div>
        </div>

        <div class="row mb-6">
          <label class="col-lg-4 col-form-label fw-bold fs-6">Unit 1</label>
          <div class="col-lg-8 fv-row">
            <select
              v-model="form.unit_01_id"
              class="form-select form-select-solid"
              :class="{ 'is-invalid': errors.unit_01_id }"
            >
              <option value="">Select Unit</option>
              <option v-for="unit in units" :key="unit.value" :value="unit.value">
                {{ unit.label }}
              </option>
            </select>
            <div v-if="errors.unit_01_id" class="invalid-feedback">{{ errors.unit_01_id[0] }}</div>
          </div>
        </div>

        <div class="row mb-6">
          <label class="col-lg-4 col-form-label fw-bold fs-6">Unit 2</label>
          <div class="col-lg-8 fv-row">
            <select
              v-model="form.unit_02_id"
              class="form-select form-select-solid"
              :class="{ 'is-invalid': errors.unit_02_id }"
            >
              <option value="">Select Unit</option>
              <option v-for="unit in units" :key="unit.value" :value="unit.value">
                {{ unit.label }}
              </option>
            </select>
            <div v-if="errors.unit_02_id" class="invalid-feedback">{{ errors.unit_02_id[0] }}</div>
          </div>
        </div>

        <div class="row mb-6">
          <label class="col-lg-4 col-form-label fw-bold fs-6">Status</label>
          <div class="col-lg-8 fv-row">
            <div class="form-check form-switch form-check-custom form-check-solid">
              <input
                v-model="form.active"
                class="form-check-input"
                type="checkbox"
                id="active"
              />
              <label class="form-check-label fw-bold text-gray-700" for="active">
                Active
              </label>
            </div>
          </div>
        </div>

        <div class="row mb-6">
          <label class="col-lg-4 col-form-label fw-bold fs-6">Returnable</label>
          <div class="col-lg-8 fv-row">
            <div class="form-check form-switch form-check-custom form-check-solid">
              <input
                v-model="form.is_returnable"
                class="form-check-input"
                type="checkbox"
                id="is_returnable"
              />
              <label class="form-check-label fw-bold text-gray-700" for="is_returnable">
                Returnable
              </label>
            </div>
          </div>
        </div>

        <div class="row mb-6">
          <label class="col-lg-4 col-form-label fw-bold fs-6">Has Inventory</label>
          <div class="col-lg-8 fv-row">
            <div class="form-check form-switch form-check-custom form-check-solid">
              <input
                v-model="form.has_inventory"
                class="form-check-input"
                type="checkbox"
                id="has_inventory"
              />
              <label class="form-check-label fw-bold text-gray-700" for="has_inventory">
                Has Inventory
              </label>
            </div>
          </div>
        </div>

        <div class="card-footer d-flex justify-content-end py-6 px-9">
          <button
            type="button"
            @click="$router.push('/products')"
            class="btn btn-light btn-active-light-primary me-2"
          >
            Cancel
          </button>
          <button
            type="submit"
            class="btn btn-primary"
            :disabled="loading"
          >
            <span v-if="loading" class="spinner-border spinner-border-sm align-middle me-2"></span>
            {{ isEdit ? 'Update' : 'Save' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import { ref, reactive, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import Swal from 'sweetalert2'
import api from '@/services/api'

export default {
  name: 'ProductForm',
  setup() {
    const route = useRoute()
    const router = useRouter()
    const loading = ref(false)
    const errors = ref({})
    const warehouses = ref([])
    const racks = ref([])
    const categories = ref([])
    const suppliers = ref([])
    const units = ref([])

    const isEdit = computed(() => !!route.params.id)

    const form = reactive({
      code: '',
      name: '',
      type: '',
      sku: '',
      barcode: '',
      category_id: '',
      description: '',
      supplier_id: '',
      active: true,
      manufacturer: '',
      unit_01_id: '',
      unit_02_id: '',
      is_returnable: false,
      has_inventory: false,
      hsn_code: '',
      batch_number: '',
      warehouse_id: '',
      rack_id: '',
      company_id: 1 // This should come from auth context
    })

    const fetchProduct = async (id) => {
      try {
        const response = await api.get(`/api/products/${id}`)
        const product = response.data
        
        Object.keys(form).forEach(key => {
          if (product.hasOwnProperty(key)) {
            form[key] = product[key]
          }
        })

        // Load racks if warehouse is selected
        if (product.warehouse_id) {
          await fetchRacks(product.warehouse_id)
        }
      } catch (error) {
        console.error('Error fetching product:', error)
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'Failed to fetch product details'
        })
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

    const fetchCategories = async () => {
      try {
        const response = await api.get('/api/categories')
        categories.value = response.data.map(cat => ({
          value: cat.id,
          label: cat.name
        }))
      } catch (error) {
        console.error('Error fetching categories:', error)
      }
    }

    const fetchSuppliers = async () => {
      try {
        const response = await api.get('/api/suppliers')
        suppliers.value = response.data.map(supplier => ({
          value: supplier.id,
          label: `${supplier.code} - ${supplier.name}`
        }))
      } catch (error) {
        console.error('Error fetching suppliers:', error)
      }
    }

    const fetchUnits = async () => {
      try {
        const response = await api.get('/api/units')
        units.value = response.data.map(unit => ({
          value: unit.id,
          label: unit.name
        }))
      } catch (error) {
        console.error('Error fetching units:', error)
      }
    }

    const onTypeChange = () => {
      // Reset product-specific fields when type changes
      if (form.type !== 'product') {
        form.hsn_code = ''
        form.batch_number = ''
        form.warehouse_id = ''
        form.rack_id = ''
        form.has_inventory = false
      }
    }

    const onWarehouseChange = () => {
      form.rack_id = ''
      fetchRacks(form.warehouse_id)
    }

    const submitForm = async () => {
      loading.value = true
      errors.value = {}

      try {
        if (isEdit.value) {
          await api.put(`/api/products/${route.params.id}`, form)
          Swal.fire({
            icon: 'success',
            title: 'Success',
            text: 'Product updated successfully'
          })
        } else {
          await api.post('/api/products', form)
          Swal.fire({
            icon: 'success',
            title: 'Success',
            text: 'Product created successfully'
          })
        }

        router.push('/products')
      } catch (error) {
        if (error.response && error.response.status === 422) {
          errors.value = error.response.data.errors
        } else {
          console.error('Error saving product:', error)
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Failed to save product'
          })
        }
      } finally {
        loading.value = false
      }
    }

    onMounted(async () => {
      await Promise.all([
        fetchWarehouses(),
        fetchCategories(),
        fetchSuppliers(),
        fetchUnits()
      ])

      if (isEdit.value) {
        await fetchProduct(route.params.id)
      }
    })

    return {
      loading,
      errors,
      form,
      isEdit,
      warehouses,
      racks,
      categories,
      suppliers,
      units,
      onTypeChange,
      onWarehouseChange,
      submitForm
    }
  }
}
</script>