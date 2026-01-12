<!--
  - /*
  -  *  Copyright (c) 2025 Divaa Solutions. All rights reserved.
  -  *
  -  *  This software is the confidential and proprietary information of Divaa Solutions
  -  *  ("Confidential Information"). You shall not disclose such Confidential Information and
  -  *  shall use it only in accordance with the terms of the license agreement you entered into
  -  *  with Divaa Solutions.
  -  *
  -  *  Unauthorized copying of this file, via any medium is strictly prohibited.
  -  *  Proprietary and confidential.
  -  *
  -  *  Last modified: 04/12/25, 11:20 pm
  -  *  Written by Chintan Bagdawala, 2025.
  -  */
  -->

<template>
    <div class="session-modal-overlay" @click.self="handleBackdropClick">
        <div class="session-modal">
            <!-- Session Closed Summary (check first!) -->
            <template v-if="posStore.closingSessionSummary">
                <div class="modal-header bg-success text-white">
                    <div class="modal-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h4 class="modal-title">Session Closed</h4>
                    <p class="modal-subtitle">{{ posStore.closingSessionSummary.session_number }}</p>
                </div>

                <div class="modal-body">
                    <div class="final-summary">
                        <div class="summary-row">
                            <span>Duration</span>
                            <span>{{ posStore.closingSessionSummary.duration }}</span>
                        </div>
                        <div class="summary-row">
                            <span>Total Transactions</span>
                            <span>{{ posStore.closingSessionSummary.total_transactions }}</span>
                        </div>
                        <div class="summary-row highlight">
                            <span>Total Sales</span>
                            <span>${{ formatCurrency(posStore.closingSessionSummary.total_sales) }}</span>
                        </div>
                        <hr>
                        <div class="summary-row">
                            <span>Cash Sales</span>
                            <span>${{ formatCurrency(posStore.closingSessionSummary.total_cash_sales) }}</span>
                        </div>
                        <div class="summary-row">
                            <span>Card Sales</span>
                            <span>${{ formatCurrency(posStore.closingSessionSummary.total_card_sales) }}</span>
                        </div>
                        <div class="summary-row">
                            <span>Other Sales</span>
                            <span>${{ formatCurrency(posStore.closingSessionSummary.total_other_sales) }}</span>
                        </div>
                        <hr>
                        <div class="summary-row">
                            <span>Opening Cash</span>
                            <span>${{ formatCurrency(posStore.closingSessionSummary.opening_cash) }}</span>
                        </div>
                        <div class="summary-row">
                            <span>Closing Cash</span>
                            <span>${{ formatCurrency(posStore.closingSessionSummary.closing_cash) }}</span>
                        </div>
                        <div class="summary-row">
                            <span>Expected Cash</span>
                            <span>${{ formatCurrency(posStore.closingSessionSummary.expected_cash) }}</span>
                        </div>
                        <div class="summary-row" :class="getDifferenceClass(posStore.closingSessionSummary.cash_difference)">
                            <span>Difference</span>
                            <span>
                                {{ posStore.closingSessionSummary.cash_difference >= 0 ? '+' : '' }}${{ formatCurrency(posStore.closingSessionSummary.cash_difference) }}
                                <i v-if="posStore.closingSessionSummary.cash_difference !== 0" class="fas fa-exclamation-triangle ms-1"></i>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button @click="handleStartNewSession" class="btn btn-primary btn-lg w-100">
                        <i class="fas fa-play me-2"></i>
                        Start New Session
                    </button>
                </div>
            </template>

            <!-- Close Session Mode -->
            <template v-else-if="posStore.sessionModalMode === 'close'">
                <div class="modal-header bg-danger text-white">
                    <div class="modal-icon">
                        <i class="fas fa-stop-circle"></i>
                    </div>
                    <h4 class="modal-title">Close Session</h4>
                    <p class="modal-subtitle">{{ posStore.currentSession?.session_number }}</p>
                </div>

                <div class="modal-body">
                    <!-- Session Summary -->
                    <div class="session-summary-card">
                        <h6 class="summary-title"><i class="fas fa-chart-bar me-2"></i>Session Summary</h6>
                        <div class="summary-grid">
                            <div class="summary-item">
                                <span class="summary-label">Duration</span>
                                <span class="summary-value">{{ posStore.currentSession?.duration || '-' }}</span>
                            </div>
                            <div class="summary-item">
                                <span class="summary-label">Transactions</span>
                                <span class="summary-value">{{ posStore.currentSession?.total_transactions || 0 }}</span>
                            </div>
                            <div class="summary-item highlight">
                                <span class="summary-label">Total Sales</span>
                                <span class="summary-value">${{ formatCurrency(posStore.currentSession?.total_sales || 0) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Cash Reconciliation -->
                    <div class="reconciliation-section">
                        <h6 class="section-title"><i class="fas fa-calculator me-2"></i>Cash Reconciliation</h6>
                        
                        <div class="reconciliation-row">
                            <span>Opening Cash</span>
                            <span>${{ formatCurrency(posStore.currentSession?.opening_cash || 0) }}</span>
                        </div>
                        
                        <div class="cash-input-section mt-3">
                            <label class="form-label fw-bold">Closing Cash Amount</label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text">$</span>
                                <input
                                    type="number"
                                    v-model.number="closingCash"
                                    class="form-control cash-input"
                                    placeholder="0.00"
                                    min="0"
                                    step="0.01"
                                />
                            </div>
                        </div>

                        <div class="notes-section mt-3">
                            <label class="form-label">Notes (optional)</label>
                            <textarea
                                v-model="closingNotes"
                                class="form-control"
                                rows="2"
                                placeholder="Any discrepancies or notes about this session..."
                            ></textarea>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button @click="posStore.closeSessionModal()" class="btn btn-light btn-lg me-2">
                        Cancel
                    </button>
                    <button @click="handleCloseSession" class="btn btn-danger btn-lg" :disabled="posStore.sessionLoading">
                        <span v-if="posStore.sessionLoading">
                            <span class="spinner-border spinner-border-sm me-2"></span>
                            Closing...
                        </span>
                        <span v-else>
                            <i class="fas fa-stop me-2"></i>
                            Close Session
                        </span>
                    </button>
                </div>
            </template>

            <!-- Open Session Mode -->
            <template v-else>
                <div class="modal-header bg-primary text-white">
                    <div class="modal-icon">
                        <i class="fas fa-play-circle"></i>
                    </div>
                    <h4 class="modal-title">Start New Session</h4>
                    <p class="modal-subtitle">Enter opening cash amount to begin</p>
                </div>
                
                <div class="modal-body">
                    <div class="session-info-card">
                        <div class="info-row">
                            <span class="info-label"><i class="fas fa-calendar me-2"></i>Date</span>
                            <span class="info-value">{{ currentDate }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label"><i class="fas fa-clock me-2"></i>Time</span>
                            <span class="info-value">{{ currentTime }}</span>
                        </div>
                    </div>

                    <div class="cash-input-section">
                        <label class="form-label fw-bold">Opening Cash Amount</label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text">$</span>
                            <input
                                type="number"
                                v-model.number="openingCash"
                                class="form-control cash-input"
                                placeholder="0.00"
                                min="0"
                                step="0.01"
                                @keyup.enter="handleOpenSession"
                                autofocus
                            />
                        </div>
                        <small class="text-muted">Count the cash in your drawer and enter the total</small>
                    </div>

                    <!-- Quick Amount Buttons -->
                    <div class="quick-amounts">
                        <button 
                            v-for="amount in [0, 100, 200, 500, 1000]" 
                            :key="amount"
                            @click="openingCash = amount"
                            class="btn btn-outline-primary"
                            :class="{ 'active': openingCash === amount }"
                        >
                            ${{ amount }}
                        </button>
                    </div>
                </div>

                <div class="modal-footer">
                    <button @click="handleOpenSession" class="btn btn-primary btn-lg w-100" :disabled="posStore.sessionLoading">
                        <span v-if="posStore.sessionLoading">
                            <span class="spinner-border spinner-border-sm me-2"></span>
                            Opening Session...
                        </span>
                        <span v-else>
                            <i class="fas fa-play me-2"></i>
                            Start Session
                        </span>
                    </button>
                </div>
            </template>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { usePosStore } from '../posStore';

const posStore = usePosStore();

// State
const openingCash = ref(0);
const closingCash = ref(0);
const closingNotes = ref('');

// Computed
const currentDate = computed(() => {
    return new Date().toLocaleDateString('en-US', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
});

const currentTime = computed(() => {
    return new Date().toLocaleTimeString('en-US', {
        hour: '2-digit',
        minute: '2-digit'
    });
});

// Methods
const formatCurrency = (amount) => Number(amount || 0).toFixed(2);

const handleBackdropClick = () => {
    // Only allow closing if it's not the open session modal (session is required)
    if (posStore.sessionModalMode !== 'open') {
        posStore.closeSessionModal();
    }
};

const handleOpenSession = async () => {
    if (openingCash.value < 0) {
        posStore.error = 'Opening cash cannot be negative';
        return;
    }
    await posStore.openSession(openingCash.value);
};

const handleCloseSession = async () => {
    if (closingCash.value < 0) {
        posStore.error = 'Closing cash cannot be negative';
        return;
    }
    await posStore.closeSession(closingCash.value, closingNotes.value);
};

const handleStartNewSession = () => {
    posStore.closingSessionSummary = null;
    posStore.sessionModalMode = 'open';
    openingCash.value = 0;
    closingCash.value = 0;
    closingNotes.value = '';
};

const getDifferenceClass = (difference) => {
    if (difference === 0) return 'text-success';
    if (difference > 0) return 'text-info';
    return 'text-danger';
};
</script>

<style lang="scss" scoped>
.session-modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background: rgba(0, 0, 0, 0.7);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10000;
    backdrop-filter: blur(4px);
}

.session-modal {
    background: white;
    border-radius: 16px;
    width: 100%;
    max-width: 480px;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    animation: slideUp 0.3s ease;
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.modal-header {
    padding: 2rem;
    text-align: center;
    border-radius: 16px 16px 0 0;

    .modal-icon {
        font-size: 3rem;
        margin-bottom: 1rem;
        opacity: 0.9;
    }

    .modal-title {
        margin: 0;
        font-weight: 700;
    }

    .modal-subtitle {
        margin: 0.5rem 0 0;
        opacity: 0.85;
        font-size: 0.95rem;
    }
}

.modal-body {
    padding: 1.5rem;
}

.modal-footer {
    padding: 1rem 1.5rem 1.5rem;
    display: flex;
    justify-content: flex-end;
}

.session-info-card {
    background: #f8f9fa;
    border-radius: 10px;
    padding: 1rem;
    margin-bottom: 1.5rem;

    .info-row {
        display: flex;
        justify-content: space-between;
        padding: 0.5rem 0;

        &:not(:last-child) {
            border-bottom: 1px solid #e9ecef;
        }
    }

    .info-label {
        color: #666;
    }

    .info-value {
        font-weight: 600;
        color: #333;
    }
}

.cash-input-section {
    margin-bottom: 1rem;

    .cash-input {
        font-size: 1.5rem;
        font-weight: 700;
        text-align: right;
    }
}

.quick-amounts {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;

    .btn {
        flex: 1;
        min-width: 60px;

        &.active {
            background: var(--bs-primary);
            color: white;
        }
    }
}

.session-summary-card {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 10px;
    padding: 1rem;
    margin-bottom: 1.5rem;

    .summary-title {
        color: #333;
        margin-bottom: 1rem;
        font-weight: 600;
    }

    .summary-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 0.75rem;
    }

    .summary-item {
        text-align: center;
        padding: 0.75rem;
        background: white;
        border-radius: 8px;

        &.highlight {
            background: var(--bs-primary);
            color: white;

            .summary-label {
                color: rgba(255, 255, 255, 0.8);
            }
        }
    }

    .summary-label {
        display: block;
        font-size: 0.75rem;
        color: #666;
        margin-bottom: 0.25rem;
    }

    .summary-value {
        font-size: 1.1rem;
        font-weight: 700;
    }
}

.reconciliation-section {
    .section-title {
        color: #333;
        margin-bottom: 1rem;
        font-weight: 600;
    }

    .reconciliation-row {
        display: flex;
        justify-content: space-between;
        padding: 0.5rem 0;
        border-bottom: 1px dashed #dee2e6;
    }
}

.final-summary {
    .summary-row {
        display: flex;
        justify-content: space-between;
        padding: 0.75rem 0;

        &.highlight {
            font-weight: 700;
            font-size: 1.1rem;
        }

        &.text-danger {
            color: #dc3545 !important;
        }

        &.text-success {
            color: #28a745 !important;
        }

        &.text-info {
            color: #17a2b8 !important;
        }
    }

    hr {
        margin: 0.5rem 0;
    }
}
</style>
