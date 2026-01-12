<template>
    <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        {{ $t('Convert to Sales Order') }}
                    </h5>
                    <button
                        type="button"
                        class="btn-close"
                        @click="closeModal"
                    ></button>
                </div>
                <div class="modal-body">
                    <div v-if="quotation" class="mb-4">
                        <h6>{{ $t('Quotation Details') }}</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <tbody>
                                    <tr>
                                        <td><strong>{{ $t('Quotation No') }}:</strong></td>
                                        <td>{{ quotation.order_no }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>{{ $t('Customer') }}:</strong></td>
                                        <td>{{ quotation.buyer?.display_name }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>{{ $t('Total Amount') }}:</strong></td>
                                        <td>{{ formatCurrency(quotation.grand_total) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <form @submit.prevent="submitConversion">
                        <div class="mb-3">
                            <label class="form-label">{{ $t('Sales Order Date') }} *</label>
                            <VueDatePicker
                                v-model="form.date"
                                :format="dateFormat"
                                :placeholder="$t('Select date')"
                                class="form-control"
                                required
                            />
                        </div>

                        <div class="mb-3">
                            <label class="form-label">{{ $t('Type') }} *</label>
                            <select v-model="form.type" class="form-select" required>
                                <option value="d">{{ $t('Delivery') }}</option>
                                <option value="p">{{ $t('Pickup') }}</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">{{ $t('Estimated Shipment Date') }}</label>
                            <VueDatePicker
                                v-model="form.estimated_shipment_date"
                                :format="dateFormat"
                                :placeholder="$t('Select date')"
                                class="form-control"
                            />
                        </div>

                        <div class="mb-3">
                            <label class="form-label">{{ $t('Payment Term') }}</label>
                            <select v-model="form.payment_term_id" class="form-select">
                                <option value="">{{ $t('Select Payment Term') }}</option>
                                <option
                                    v-for="term in paymentTerms"
                                    :key="term.id"
                                    :value="term.id"
                                >
                                    {{ term.name }}
                                </option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">{{ $t('Tax Rate') }} (%)</label>
                            <input
                                type="number"
                                step="0.01"
                                min="0"
                                v-model="form.tax_rate"
                                class="form-control"
                                placeholder="5.00"
                            />
                        </div>

                        <div class="mb-3">
                            <label class="form-label">{{ $t('Remarks') }}</label>
                            <textarea
                                v-model="form.remarks"
                                class="form-control"
                                rows="3"
                                :placeholder="$t('Enter any additional remarks')"
                            ></textarea>
                        </div>

                        <div v-if="errors.length" class="alert alert-danger">
                            <ul class="mb-0">
                                <li v-for="error in errors" :key="error">{{ error }}</li>
                            </ul>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button
                        type="button"
                        class="btn btn-secondary"
                        @click="closeModal"
                    >
                        {{ $t('Cancel') }}
                    </button>
                    <button
                        type="button"
                        class="btn btn-primary"
                        @click="submitConversion"
                        :disabled="isSubmitting"
                    >
                        <span v-if="isSubmitting" class="spinner-border spinner-border-sm me-2"></span>
                        {{ isSubmitting ? $t('Converting...') : $t('Convert') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import VueDatePicker from '@vuepic/vue-datepicker'
import '@vuepic/vue-datepicker/dist/main.css'
import ApiService from '@/core/services/ApiService'

const props = defineProps({
    quotation: {
        type: Object,
        required: true
    }
})

const emit = defineEmits(['close', 'converted'])

const { t } = useI18n()

const form = reactive({
    date: new Date().toISOString().split('T')[0],
    type: 'd', // Default to delivery
    estimated_shipment_date: props.quotation?.expected_delivery_date || null,
    payment_term_id: null,
    tax_rate: 5.0,
    remarks: props.quotation?.remark || ''
})

const paymentTerms = ref([])
const errors = ref([])
const isSubmitting = ref(false)
const dateFormat = 'yyyy-MM-dd'

onMounted(async () => {
    await fetchPaymentTerms()
})

const fetchPaymentTerms = async () => {
    try {
        const response = await ApiService.get('api/v1/payment-terms')
        paymentTerms.value = response.data.data
    } catch (error) {
        console.error('Error fetching payment terms:', error)
    }
}

const closeModal = () => {
    emit('close')
}

const submitConversion = async () => {
    errors.value = []
    isSubmitting.value = true

    try {
        const response = await ApiService.post(
            `api/v1/quotations-convert-to-sales-order/${props.quotation.id}`,
            form
        )

        emit('converted', response.data.data)
    } catch (error) {
        if (error.response?.status === 422) {
            const errorData = error.response.data
            if (errorData.errors) {
                errors.value = Object.values(errorData.errors).flat()
            } else {
                errors.value = [errorData.message || 'Conversion failed']
            }
        } else if (error.response?.status === 403) {
            errors.value = ['You do not have permission to create sales orders']
        } else {
            errors.value = ['An unexpected error occurred. Please try again.']
        }
    } finally {
        isSubmitting.value = false
    }
}

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(amount || 0)
}
</script>

<style scoped>
.modal {
    z-index: 1050;
}

.modal-dialog {
    max-width: 600px;
}

.table th {
    background-color: #f8f9fa;
}
</style>