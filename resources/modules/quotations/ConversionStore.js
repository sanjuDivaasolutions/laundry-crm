import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@common@/services/api'

export const useQuotationConversionStore = defineStore('quotationConversion', () => {
  const isLoading = ref(false)
  const error = ref(null)

  const convertToSalesOrder = async (quotationId, conversionData) => {
    isLoading.value = true
    error.value = null

    try {
      const response = await api.post(`/api/quotations/${quotationId}/convert-to-sales-order`, conversionData)
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to convert quotation'
      throw err
    } finally {
      isLoading.value = false
    }
  }

  const getConversionPreview = async (quotationId) => {
    isLoading.value = true
    error.value = null

    try {
      const response = await api.get(`/api/quotations/${quotationId}/conversion-preview`)
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to get conversion preview'
      throw err
    } finally {
      isLoading.value = false
    }
  }

  return {
    isLoading,
    error,
    convertToSalesOrder,
    getConversionPreview
  }
})