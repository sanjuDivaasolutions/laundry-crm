import { defineStore } from 'pinia'
import { api } from '@/services/api'
import { showSuccessMessage, showErrorMessage } from '@/utility/NotificationUtils'

export interface Agent {
  id: number
  code: string
  name: string
  email: string
  phone?: string
  address?: string
  commission_rate: number
  commission_type: 'percentage' | 'fixed'
  fixed_commission: number
  active: boolean
  user_id?: number
  user?: {
    id: number
    name: string
    email: string
  }
  notes?: string
  total_pending_commissions?: number
  total_approved_commissions?: number
  total_paid_commissions?: number
  commissions?: Commission[]
  created_at: string
  updated_at: string
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
  commissionable_type: string
  commissionable_id: number
  commissionable?: {
    type: string
    id: number
    number: string
  }
  approved_by?: {
    id: number
    name: string
  }
  paid_by?: {
    id: number
    name: string
  }
}

export interface AgentFilters {
  search?: string
  active?: boolean | string
  commission_type?: string
  page?: number
  per_page?: number
}

export const useAgentStore = defineStore('agents', {
  state: () => ({
    agents: [] as Agent[],
    agentData: {
      current_page: 1,
      last_page: 1,
      per_page: 25,
      total: 0,
      from: 0,
      to: 0
    },
    loading: false,
    currentAgent: null as Agent | null
  }),

  getters: {
    totalAgents: (state) => state.agentData.total,
    currentPage: (state) => state.agentData.current_page,
    lastPage: (state) => state.agentData.last_page,
    
    activeAgents: (state) => state.agents.filter(agent => agent.active),
    inactiveAgents: (state) => state.agents.filter(agent => !agent.active),
    
    agentsByCommissionType: (state) => (type: string) => {
      return state.agents.filter(agent => agent.commission_type === type)
    },
    
    totalPendingCommissions: (state) => {
      return state.agents.reduce((total, agent) => total + (agent.total_pending_commissions || 0), 0)
    },
    
    totalApprovedCommissions: (state) => {
      return state.agents.reduce((total, agent) => total + (agent.total_approved_commissions || 0), 0)
    },
    
    totalPaidCommissions: (state) => {
      return state.agents.reduce((total, agent) => total + (agent.total_paid_commissions || 0), 0)
    }
  },

  actions: {
    async fetchAgents(filters: AgentFilters = {}) {
      this.loading = true
      try {
        const response = await api.get('/v1/agents', { params: filters })
        this.agents = response.data.data
        this.agentData = response.data
        return response.data
      } catch (error) {
        showErrorMessage('Failed to fetch agents')
        throw error
      } finally {
        this.loading = false
      }
    },

    async fetchAgent(id: number) {
      this.loading = true
      try {
        const response = await api.get(`/v1/agents/${id}`)
        this.currentAgent = response.data
        return response.data
      } catch (error) {
        showErrorMessage('Failed to fetch agent')
        throw error
      } finally {
        this.loading = false
      }
    },

    async createAgent(agentData: Partial<Agent>) {
      this.loading = true
      try {
        const response = await api.post('/v1/agents', agentData)
        this.agents.unshift(response.data)
        showSuccessMessage('Agent created successfully')
        return response.data
      } catch (error) {
        showErrorMessage('Failed to create agent')
        throw error
      } finally {
        this.loading = false
      }
    },

    async updateAgent(id: number, agentData: Partial<Agent>) {
      this.loading = true
      try {
        const response = await api.put(`/v1/agents/${id}`, agentData)
        const index = this.agents.findIndex(agent => agent.id === id)
        if (index !== -1) {
          this.agents[index] = response.data
        }
        showSuccessMessage('Agent updated successfully')
        return response.data
      } catch (error) {
        showErrorMessage('Failed to update agent')
        throw error
      } finally {
        this.loading = false
      }
    },

    async deleteAgent(id: number) {
      this.loading = true
      try {
        await api.delete(`/v1/agents/${id}`)
        this.agents = this.agents.filter(agent => agent.id !== id)
        showSuccessMessage('Agent deleted successfully')
      } catch (error) {
        showErrorMessage('Failed to delete agent')
        throw error
      } finally {
        this.loading = false
      }
    },

    async getCommissionSummary(agentId: number, filters: any = {}) {
      this.loading = true
      try {
        const response = await api.get(`/v1/agents/${agentId}/commission-summary`, { params: filters })
        return response.data
      } catch (error) {
        showErrorMessage('Failed to fetch commission summary')
        throw error
      } finally {
        this.loading = false
      }
    },

    async approveCommissions(commissionIds: number[]) {
      this.loading = true
      try {
        const response = await api.post('/v1/agents/commissions/approve', {
          commission_ids: commissionIds
        })
        showSuccessMessage(response.data.message)
        return response.data
      } catch (error) {
        showErrorMessage('Failed to approve commissions')
        throw error
      } finally {
        this.loading = false
      }
    },

    async markCommissionsAsPaid(commissionIds: number[]) {
      this.loading = true
      try {
        const response = await api.post('/v1/agents/commissions/mark-paid', {
          commission_ids: commissionIds
        })
        showSuccessMessage(response.data.message)
        return response.data
      } catch (error) {
        showErrorMessage('Failed to mark commissions as paid')
        throw error
      } finally {
        this.loading = false
      }
    },

    async exportAgents(filters: AgentFilters = {}) {
      try {
        const response = await api.get('/v1/agents-csv', { 
          params: filters,
          responseType: 'blob'
        })
        
        // Create download link
        const url = window.URL.createObjectURL(new Blob([response.data]))
        const link = document.createElement('a')
        link.href = url
        link.setAttribute('download', `agents-${new Date().toISOString().split('T')[0]}.csv`)
        document.body.appendChild(link)
        link.click()
        link.remove()
        window.URL.revokeObjectURL(url)
        
        showSuccessMessage('Agents exported successfully')
      } catch (error) {
        showErrorMessage('Failed to export agents')
        throw error
      }
    },

    clearAgents() {
      this.agents = []
      this.agentData = {
        current_page: 1,
        last_page: 1,
        per_page: 25,
        total: 0,
        from: 0,
        to: 0
      }
    },

    setCurrentAgent(agent: Agent | null) {
      this.currentAgent = agent
    }
  }
})