import { defineStore } from 'pinia'
import { ref } from 'vue'
import axios from 'axios'

export const useDashboardStore = defineStore('dashboard', () => {
  const loading = ref(false)
  const error = ref(null)

  const fetchDashboardCards = async () => {
    loading.value = true
    error.value = null
    
    try {
      const response = await axios.get('/api/v1/dashboard-cards')
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch dashboard data'
      throw err
    } finally {
      loading.value = false
    }
  }

  const fetchDashboardData = async () => {
    loading.value = true
    error.value = null
    
    try {
      const response = await axios.get('/api/v1/dashboard-data')
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch dashboard data'
      throw err
    } finally {
      loading.value = false
    }
  }

  return {
    loading,
    error,
    fetchDashboardCards,
    fetchDashboardData
  }
})