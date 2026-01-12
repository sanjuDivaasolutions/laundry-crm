<template>
  <div class="conversion-modal">
    <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
      <div class="modal-dialog modal-xl">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">
              <i class="fas fa-exchange-alt me-2"></i>
              Convert Quotation to Sales Order
            </h5>
            <button type="button" class="btn-close" @click="$emit('close')"></button>
          </div>
          
          <div class="modal-body">
            <!-- Quotation Summary -->
            <div class="card mb-4">
              <div class="card-header">
                <h6 class="mb-0">Quotation Summary</h6>
              </div>
              <div class="card-body">
                <div class="row">
                  <div class="col-md-6">
                    <p><strong>Quotation Number:</strong> {{ quotation.quotation_number }}</p>
                    <p><strong>Customer:</strong> {{ quotation.buyer?.name }}</p>
                    <p><strong>Date:</strong> {{ formatDate(quotation.quotation_date) }}</p>
                  </div>
                  <div class="col-md-6">
                    <p><strong>Subtotal:</strong> {{ formatCurrency(quotation.subtotal) }}</p>
                    <p><strong>Tax:</strong> {{ formatCurrency(quotation.tax_amount) }}</p>
                    <p><strong>Total:</strong> {{ formatCurrency(quotation.total_amount) }}</p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Conversion Form -->
            <form @submit.prevent="handleSubmit">
              <!-- Warehouse Selection -->
              <div class="row mb-3">
                <div class="col-md-6">
                  <label class="form-label">Warehouse *</label>
                  <select 
                    v-model="form.warehouse_id" 
                    class="form-select"
                    :class="{ 'is-invalid': errors.warehouse_id }"
                    required
                  >
                    <option value="">Select Warehouse</option>
                    <option 
                      v-for="warehouse in warehouses" 
                      :key="warehouse.id" 
                      :value="warehouse.id"
                    >
                      {{ warehouse.name }}
                    </option>
                  </select>
                  <div v-if="errors.warehouse_id" class="invalid-feedback">
                    {{ errors.warehouse_id }}
                  </div>
                </div>
                
                <div class="col-md-6">
                  <label class="form-label">Expected Delivery Date</label>
                  <input 
                    v-model="form.expected_delivery_date" 
                    type="date" 
                    class="form-control"
                    :class="{ 'is-invalid': errors.expected_delivery_date }"
                    :min="minDate"
                  >
                  <div v-if="errors.expected_delivery_date" class="invalid-feedback">
                    {{ errors.expected_delivery_date }}
                  </div>
                </div>
              </div>

              <!-- Sales Person -->
              <div class="row mb-3">
                <div class="col-md-6">
                  <label class="form-label">Sales Person</label>
                  <select 
                    v-model="form.sales_person_id" 
                    class="form-select"
                  >
                    <option value="">Select Sales Person</option>
                    <option 
                      v-for="salesPerson in salesPersons" 
                      :key="salesPerson.id" 
                      :value="salesPerson.id"
                    >
                      {{ salesPerson.name }}
                    </option>
                  </select>
                </div>
                
                <div class="col-md-6">
                  <label class="form-label">Payment Terms</label>
                  <input 
                    v-model="form.payment_terms" 
                    type="text" 
                    class="form-control"
                    placeholder="e.g., Net 30, 50% Advance"
                  >
                </div>
              </div>

              <!-- Items Selection -->
              <div class="card mb-3">
                <div class="card-header">
                  <div class="d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Items to Convert</h6>
                    <div class="form-check">
                      <input 
                        v-model="convertAllItems" 
                        type="checkbox" 
                        class="form-check-input"
                        id="convertAllItems"
                      >
                      <label class="form-check-label" for="convertAllItems">
                        Convert All Items
                      </label>
                    </div>
                  </div>
                </div>
                <div class="card-body">
                  <div v-if="convertAllItems" class="alert alert-info">
                    All {{ quotation.items?.length || 0 }} items will be converted to the sales order.
                  </div>
                  
                  <div v-else class="table-responsive">
                    <table class="table table-striped">
                      <thead>
                        <tr>
                          <th width="50">
                            <input 
                              type="checkbox" 
                              v-model="selectAllItems"
                              @change="toggleAllItems"
                            >
                          </th>
                          <th>Product</th>
                          <th>Description</th>
                          <th>Quantity</th>
                          <th>Unit Price</th>
                          <th>Total</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr v-for="item in quotation.items" :key="item.id">
                          <td>
                            <input 
                              type="checkbox" 
                              v-model="selectedItems"
                              :value="item.id"
                            >
                          </td>
                          <td>{{ item.product?.name }}</td>
                          <td>{{ item.description }}</td>
                          <td>
                            <input 
                              v-if="selectedItems.includes(item.id)"
                              v-model.number="itemQuantities[item.id]"
                              type="number" 
                              class="form-control form-control-sm"
                              min="1"
                              :max="item.quantity"
                            >
                            <span v-else>{{ item.quantity }}</span>
                          </td>
                          <td>
                            <input 
                              v-if="selectedItems.includes(item.id)"
                              v-model.number="itemPrices[item.id]"
                              type="number" 
                              class="form-control form-control-sm"
                              min="0"
                              step="0.01"
                            >
                            <span v-else>{{ formatCurrency(item.unit_price) }}</span>
                          </td>
                          <td>{{ formatCurrency(calculateItemTotal(item)) }}</td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>

              <!-- Additional Notes -->
              <div class="row mb-3">
                <div class="col-md-6">
                  <label class="form-label">Customer Notes</label>
                  <textarea 
                    v-model="form.customer_notes" 
                    class="form-control"
                    rows="3"
                    placeholder="Additional notes for the customer..."
                  ></textarea>
                </div>
                
                <div class="col-md-6">
                  <label class="form-label">Terms & Conditions</label>
                  <textarea 
                    v-model="form.terms_and_conditions" 
                    class="form-control"
                    rows="3"
                    placeholder="Terms and conditions for this sales order..."
                  ></textarea>
                </div>
              </div>
            </form>
          </div>
          
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="$emit('close')">
              Cancel
            </button>
            <button 
              type="button" 
              class="btn btn-primary"
              @click="handleSubmit"
              :disabled="isSubmitting || (!convertAllItems && selectedItems.length === 0)"
            >
              <span v-if="isSubmitting" class="spinner-border spinner-border-sm me-2"></span>
              <i class="fas fa-check me-2"></i>
              Convert to Sales Order
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useQuotationConversionStore } from './ConversionStore'
import { useWarehouseStore } from '@modules@/warehouses/IndexStore'
import { useUserStore } from '@modules@/users/IndexStore'
import { useToast } from '@common@/composables/useToast'

const props = defineProps({
  quotation: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['close', 'converted'])

const conversionStore = useQuotationConversionStore()
const warehouseStore = useWarehouseStore()
const userStore = useUserStore()
const { showToast } = useToast()

const isSubmitting = ref(false)
const errors = ref({})
const warehouses = ref([])
const salesPersons = ref([])

// Form data
const form = ref({
  warehouse_id: '',
  expected_delivery_date: '',
  sales_person_id: '',
  payment_terms: props.quotation.payment_terms || '',
  customer_notes: props.quotation.notes || '',
  terms_and_conditions: props.quotation.terms_and_conditions || ''
})

// Items selection
const convertAllItems = ref(true)
const selectedItems = ref([])
const selectAllItems = ref(false)
const itemQuantities = ref({})
const itemPrices = ref({})

const minDate = computed(() => {
  const today = new Date()
  today.setDate(today.getDate() + 1)
  return today.toISOString().split('T')[0]
})

const toggleAllItems = () => {
  if (selectAllItems.value) {
    selectedItems.value = props.quotation.items?.map(item => item.id) || []
  } else {
    selectedItems.value = []
  }
}

const calculateItemTotal = (item) => {
  const quantity = itemQuantities.value[item.id] || item.quantity
  const price = itemPrices.value[item.id] || item.unit_price
  return quantity * price
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString()
}

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD'
  }).format(amount)
}

const validateForm = () => {
  errors.value = {}
  
  if (!form.value.warehouse_id) {
    errors.value.warehouse_id = 'Please select a warehouse'
  }
  
  if (form.value.expected_delivery_date && new Date(form.value.expected_delivery_date) <= new Date()) {
    errors.value.expected_delivery_date = 'Expected delivery date must be in the future'
  }
  
  if (!convertAllItems.value && selectedItems.value.length === 0) {
    errors.value.items = 'Please select at least one item to convert'
  }
  
  return Object.keys(errors.value).length === 0
}

const handleSubmit = async () => {
  if (!validateForm()) {
    return
  }
  
  isSubmitting.value = true
  
  try {
    const conversionData = {
      ...form.value,
      convert_all_items: convertAllItems.value,
      selected_items: convertAllItems.value 
        ? [] 
        : selectedItems.value.map(itemId => ({
            id: itemId,
            quantity: itemQuantities.value[itemId] || 1,
            unit_price: itemPrices.value[itemId] || 0
          }))
    }
    
    const response = await conversionStore.convertToSalesOrder(props.quotation.id, conversionData)
    emit('converted', response.data)
    emit('close')
  } catch (error) {
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors
    } else {
      showToast('error', 'Failed to convert quotation to sales order')
    }
  } finally {
    isSubmitting.value = false
  }
}

// Initialize item quantities and prices
watch(() => props.quotation.items, (items) => {
  if (items) {
    items.forEach(item => {
      itemQuantities.value[item.id] = item.quantity
      itemPrices.value[item.id] = item.unit_price
    })
  }
}, { immediate: true })

onMounted(async () => {
  try {
    const [warehousesResponse, usersResponse] = await Promise.all([
      warehouseStore.fetch(),
      userStore.fetch()
    ])
    
    warehouses.value = warehousesResponse.data
    salesPersons.value = usersResponse.data
  } catch (error) {
    showToast('error', 'Failed to load required data')
  }
})
</script>