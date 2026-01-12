/*
 *
 *  *  Copyright (c) 2025 Divaa Solutions. All rights reserved.
 *  *
 *  *  This software is the confidential and proprietary information of Divaa Solutions
 *  *  ("Confidential Information"). You shall not disclose such Confidential Information and
 *  *  shall use it only in accordance with the terms of the license agreement you entered into
 *  *  with Divaa Solutions.
 *  *
 *  *  Unauthorized copying of this file, via any medium is strictly prohibited.
 *  *  Proprietary and confidential.
 *  *
 *  *  Last modified: 30/11/25, 12:00 pm
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

import { defineStore } from 'pinia';
import axios from 'axios';

export const usePosStore = defineStore('pos', {
    state: () => ({
        // Products
        allProducts: [], // All products loaded once
        products: [], // Filtered products for display
        categories: [],
        loading: false,
        loadingProducts: false,

        // Cart
        cartItems: [],
        selectedCustomer: null,

        // Payment
        paymentModes: [],
        selectedPaymentModeId: null,
        amountReceived: 0,
        discount: 0,
        paymentRemarks: '',
        prescriptionVerified: false,
        taxRate: 13,

        // Search & Filter
        searchQuery: '',
        selectedCategory: '',

        // UI State
        scannerActive: false,
        currentTill: 'Till #1',
        showQuickKeys: true,
        showReceipt: false,
        showOrders: false,
        lastTransaction: null,
        viewMode: 'grid', // 'grid' or 'list'

        // Stats - Live updated
        todaySales: 0,
        transactionCount: 0,

        // Session Management
        currentSession: null,
        sessionLoading: false,
        showSessionModal: false,
        sessionModalMode: 'open', // 'open' or 'close'
        closingSessionSummary: null,
        sessionOrders: [],
        showSessionOrders: false,
        sessionOrdersLoading: false,

        // Error handling
        error: null,
        success: null
    }),

    getters: {
        filteredProducts: (state) => {
            let filtered = state.allProducts;

            // Filter by search query (live search)
            if (state.searchQuery) {
                const query = state.searchQuery.toLowerCase();
                filtered = filtered.filter(product =>
                    product.name.toLowerCase().includes(query) ||
                    product.sku.toLowerCase().includes(query) ||
                    (product.barcode && product.barcode.toLowerCase().includes(query))
                );
            }

            // Filter by category
            if (state.selectedCategory) {
                filtered = filtered.filter(product =>
                    product.category_id === state.selectedCategory
                );
            }

            return filtered;
        },

        subtotal: (state) => {
            return state.cartItems.reduce((sum, item) => sum + item.total, 0);
        },

        taxAmount() {
            return (this.subtotal * this.taxRate) / 100;
        },

        total() {
            return this.subtotal + this.taxAmount - this.discount;
        },

        change() {
            return this.amountReceived - this.total;
        },

        hasRxMedicines: (state) => {
            return state.cartItems.some(item => item.requires_prescription);
        },

        canProcessPayment() {
            if (this.cartItems.length === 0) return false;
            if (!this.selectedPaymentModeId) return false;
            if (this.hasRxMedicines && !this.prescriptionVerified) return false;
            // For cash payments, check if amount received is enough
            const selectedMode = this.paymentModes.find(m => m.id === this.selectedPaymentModeId);
            if (selectedMode && selectedMode.name.toLowerCase() === 'cash' && this.amountReceived < this.total) return false;
            return true;
        },

        selectedPaymentMode() {
            return this.paymentModes.find(m => m.id === this.selectedPaymentModeId) || null;
        },

        isCashPayment() {
            const mode = this.selectedPaymentMode;
            return mode && mode.name.toLowerCase() === 'cash';
        },

        cartItemsCount: (state) => {
            return state.cartItems.reduce((sum, item) => sum + item.quantity, 0);
        },

        // Session getters
        hasActiveSession: (state) => {
            return state.currentSession !== null;
        },

        sessionInfo: (state) => {
            if (!state.currentSession) return null;
            return {
                id: state.currentSession.id,
                number: state.currentSession.session_number,
                openedAt: state.currentSession.opened_at,
                duration: state.currentSession.duration,
                totalSales: state.currentSession.total_sales || 0,
                totalTransactions: state.currentSession.total_transactions || 0
            };
        }
    },

    actions: {
        async loadAllProducts() {
            this.loadingProducts = true;
            this.error = null;

            try {
                // Load all products once without any filters
                const response = await axios.get('/pos/products');

                if (response.data.success) {
                    this.allProducts = response.data.data;
                    // Set filtered products initially
                    this.products = this.filteredProducts;
                }
            } catch (error) {
                console.error('Error loading products:', error);
                this.error = 'Failed to load products';
            } finally {
                this.loadingProducts = false;
            }
        },

        async loadCategories() {
            try {
                const response = await axios.get('/pos/categories');
                if (response.data.success) {
                    this.categories = response.data.data;
                }
            } catch (error) {
                console.error('Error loading categories:', error);
            }
        },

        // Customers are now searched via API in PosCustomerSelect component
        // No preloading needed

        async loadPaymentModes() {
            try {
                const response = await axios.get('/pos/payment-modes');
                if (response.data.success) {
                    this.paymentModes = response.data.data;
                    // Auto-select first payment mode (usually Cash)
                    if (this.paymentModes.length > 0 && !this.selectedPaymentModeId) {
                        this.selectedPaymentModeId = this.paymentModes[0].id;
                    }
                }
            } catch (error) {
                console.error('Error loading payment modes:', error);
            }
        },

        addToCart(product) {
            // Check stock
            if (product.stock_quantity <= 0) {
                this.error = `${product.name} is out of stock!`;
                return false;
            }

            const existingIndex = this.cartItems.findIndex(item => item.id === product.id);

            if (existingIndex >= 0) {
                const newQuantity = this.cartItems[existingIndex].quantity + 1;
                if (newQuantity > product.stock_quantity) {
                    this.error = `Cannot add more ${product.name}. Only ${product.stock_quantity} in stock.`;
                    return false;
                }
                this.updateQuantity(existingIndex, newQuantity);
            } else {
                this.cartItems.push({
                    id: product.id,
                    name: product.name,
                    sku: product.sku,
                    unit_price: product.selling_price || 0,
                    quantity: 1,
                    total: product.selling_price || 0,
                    requires_prescription: product.requires_prescription || false,
                    stock_quantity: product.stock_quantity
                });
            }

            // Do not show a notification when items are added to cart (UI preference)
            return true;
        },

        updateQuantity(index, quantity) {
            if (quantity <= 0) {
                this.removeFromCart(index);
                return;
            }

            const item = this.cartItems[index];
            if (quantity > item.stock_quantity) {
                this.error = `Cannot set quantity to ${quantity}. Only ${item.stock_quantity} in stock.`;
                return;
            }

            this.cartItems[index].quantity = quantity;
            this.cartItems[index].total = this.cartItems[index].unit_price * quantity;
        },

        removeFromCart(index) {
            const item = this.cartItems[index];
            this.cartItems.splice(index, 1);
            this.success = `${item.name} removed from cart`;
        },

        clearCart() {
            this.cartItems = [];
            this.selectedCustomer = null;
            this.discount = 0;
            this.amountReceived = 0;
            // referenceNo removed from UI
            this.paymentRemarks = '';
            this.prescriptionVerified = false;
            // Keep selectedPaymentModeId for convenience
        },

        async processSale() {
            if (!this.canProcessPayment) return;

            this.loading = true;
            this.error = null;

            try {
                const saleData = {
                    customer_id: this.selectedCustomer,
                    items: this.cartItems,
                    subtotal: this.subtotal,
                    tax_amount: this.taxAmount,
                    discount: this.discount,
                    total: this.total,
                    payment_mode_id: this.selectedPaymentModeId,
                    amount_received: this.isCashPayment ? this.amountReceived : this.total,
                    reference_no: null,
                    remarks: this.paymentRemarks || null,
                    prescription_verified: this.prescriptionVerified
                };

                const response = await axios.post('/pos/sales', saleData);

                if (response.data.success) {
                    this.lastTransaction = response.data.data;
                    // Show notification with invoice details instead of modal
                    const invoiceNo = response.data.data.invoice_number;
                    const total = response.data.data.total;
                    this.success = `Order completed! Invoice: ${invoiceNo} | Total: $${Number(total).toFixed(2)}`;

                    // Clear cart
                    this.clearCart();

                    // Reload all products to update stock
                    await this.loadAllProducts();

                    // Refresh stats after successful sale
                    await this.loadTodayStats();

                    return response.data.data;
                }
            } catch (error) {
                console.error('Error processing sale:', error);
                this.error = error.response?.data?.message || 'Failed to process sale';
            } finally {
                this.loading = false;
            }
        },

        searchProducts() {
            // Instant local search - no API call or timeout needed
            this.products = this.filteredProducts;
        },

        filterByCategory() {
            // Instant local filtering - no API call needed
            this.products = this.filteredProducts;
        },

        async searchByBarcode(barcode) {
            try {
                const response = await axios.get(`/pos/barcode/${barcode}`);
                if (response.data.success) {
                    return response.data.data;
                }
            } catch (error) {
                this.error = 'Product not found';
                return null;
            }
        },

        toggleScanner() {
            this.scannerActive = !this.scannerActive;
        },

        toggleViewMode() {
            this.viewMode = this.viewMode === 'grid' ? 'list' : 'grid';
        },

        toggleOrders() {
            this.showOrders = !this.showOrders;
        },

        async loadTodayStats() {
            try {
                const response = await axios.get('/pos/sales/summary');
                if (response.data.success) {
                    this.todaySales = response.data.data.total_sales || 0;
                    this.transactionCount = response.data.data.total_transactions || 0;
                }
            } catch (error) {
                console.error('Error loading stats:', error);
            }
        },

        clearMessages() {
            this.error = null;
            this.success = null;
        },

        // ==========================================
        // SESSION MANAGEMENT ACTIONS
        // ==========================================

        async checkActiveSession() {
            this.sessionLoading = true;
            try {
                const response = await axios.get('/pos/session/active');
                if (response.data.success && response.data.has_session) {
                    this.currentSession = response.data.data;
                    return true;
                } else {
                    this.currentSession = null;
                    return false;
                }
            } catch (error) {
                console.error('Error checking session:', error);
                this.currentSession = null;
                return false;
            } finally {
                this.sessionLoading = false;
            }
        },

        async openSession(openingCash) {
            this.sessionLoading = true;
            this.error = null;
            try {
                const response = await axios.post('/pos/session/open', {
                    opening_cash: openingCash
                });

                if (response.data.success) {
                    this.currentSession = response.data.data;
                    this.showSessionModal = false;
                    this.success = `Session ${response.data.data.session_number} opened successfully!`;
                    return true;
                } else {
                    this.error = response.data.message || 'Failed to open session';
                    return false;
                }
            } catch (error) {
                console.error('Error opening session:', error);
                this.error = error.response?.data?.message || 'Failed to open session';
                return false;
            } finally {
                this.sessionLoading = false;
            }
        },

        async closeSession(closingCash, notes = '') {
            this.sessionLoading = true;
            this.error = null;
            try {
                const response = await axios.post('/pos/session/close', {
                    closing_cash: closingCash,
                    notes: notes
                });

                if (response.data.success) {
                    this.closingSessionSummary = response.data.data;
                    this.currentSession = null;
                    this.showSessionModal = false;
                    this.success = `Session ${response.data.data.session_number} closed successfully!`;
                    return response.data.data;
                } else {
                    this.error = response.data.message || 'Failed to close session';
                    return null;
                }
            } catch (error) {
                console.error('Error closing session:', error);
                this.error = error.response?.data?.message || 'Failed to close session';
                return null;
            } finally {
                this.sessionLoading = false;
            }
        },

        async openSessionModal(mode = 'open') {
            this.sessionModalMode = mode;
            this.showSessionModal = true;

            // If closing, refresh session data to get real-time stats
            if (mode === 'close') {
                await this.checkActiveSession();
            }
        },

        closeSessionModal() {
            this.showSessionModal = false;
            this.closingSessionSummary = null;
        },

        // ==========================================
        // SESSION ORDERS ACTIONS
        // ==========================================

        async loadSessionOrders() {
            this.sessionOrdersLoading = true;
            try {
                const response = await axios.get('/pos/session/orders');
                if (response.data.success) {
                    this.sessionOrders = response.data.data;
                    return response.data;
                } else {
                    this.error = response.data.message || 'Failed to load orders';
                    return null;
                }
            } catch (error) {
                console.error('Error loading session orders:', error);
                this.error = error.response?.data?.message || 'Failed to load session orders';
                return null;
            } finally {
                this.sessionOrdersLoading = false;
            }
        },

        async toggleSessionOrders() {
            if (!this.showSessionOrders) {
                await this.loadSessionOrders();
            }
            this.showSessionOrders = !this.showSessionOrders;
        },

        closeSessionOrders() {
            this.showSessionOrders = false;
        }
    }
});