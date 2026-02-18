<template>
    <div class="d-flex flex-column h-100">
        <!-- Header - Dark Blue -->
        <div class="card card-flush mb-4" style="background: linear-gradient(135deg, #1e3a5f 0%, #2c5282 100%);">
            <div class="card-body py-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <i class="ki-duotone ki-shop fs-2x text-white me-3">
                            <span class="path1"></span>
                            <span class="path2"></span>
                            <span class="path3"></span>
                        </i>
                        <div>
                            <h1 class="fs-2 fw-bold text-white mb-0">LaundryPOS</h1>
                            <span class="text-white opacity-75 fs-7">Branch: Main Street</span>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-4">
                        <!-- Search Orders Button -->
                        <button class="btn btn-sm btn-light d-flex align-items-center gap-2" @click="openHistoryModal">
                            <i class="ki-duotone ki-magnifier fs-4"><span class="path1"></span><span class="path2"></span></i>
                            Orders
                        </button>
                        <div class="text-end">
                            <span class="text-white opacity-75 fs-7">Today</span>
                            <h3 class="fs-4 fw-bold text-white mb-0">{{ todayDate }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Status Summary Bar -->
        <div class="card card-flush mb-4">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div class="d-flex gap-4 flex-wrap">
                        <div class="d-flex align-items-center">
                            <i class="ki-duotone ki-time fs-3 text-warning me-2">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                            <span class="text-gray-600 me-1">Pending:</span>
                            <span class="fw-bold text-warning">{{ statistics.pending }}</span>
                        </div>
                        <div class="d-flex align-items-center">
                            <i class="ki-duotone ki-drop fs-3 text-info me-2">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                            <span class="text-gray-600 me-1">Washing:</span>
                            <span class="fw-bold text-info">{{ statistics.washing }}</span>
                        </div>
                        <div class="d-flex align-items-center">
                            <i class="ki-duotone ki-cloud fs-3 me-2" style="color: #8b5cf6;">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                            <span class="text-gray-600 me-1">Drying:</span>
                            <span class="fw-bold" style="color: #8b5cf6;">{{ statistics.drying }}</span>
                        </div>
                        <div class="d-flex align-items-center">
                            <i class="ki-duotone ki-check-circle fs-3 text-success me-2">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                            <span class="text-gray-600 me-1">Ready:</span>
                            <span class="fw-bold text-success">{{ statistics.ready }}</span>
                        </div>
                        <div class="d-flex align-items-center">
                            <i class="ki-duotone ki-verify fs-3 text-gray-500 me-2">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                            <span class="text-gray-600 me-1">Completed:</span>
                            <span class="fw-bold text-gray-600">{{ statistics.completed }}</span>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-4">
                        <!-- Search Filter -->
                        <div class="d-flex align-items-center position-relative">
                            <i class="ki-duotone ki-magnifier fs-3 position-absolute ms-3">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                            <input
                                type="text"
                                class="form-control form-control-sm form-control-solid ps-10"
                                placeholder="Search orders..."
                                v-model="searchQuery"
                                style="width: 180px;"
                            />
                        </div>
                        <div class="d-flex align-items-center">
                            <span class="text-gray-600 me-2">Today's Revenue:</span>
                            <span class="fs-4 fw-bold text-success">{{ formatCurrency(statistics.today_revenue) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Board Area - 5 Columns -->
        <div class="d-flex flex-grow-1 gap-4 pb-3">
            <!-- New Order Panel -->
            <div class="card card-flush" style="flex: 0 0 320px; min-width: 320px;">
                <div class="card-header py-4 border-0">
                    <h3 class="card-title fs-5 fw-bold">
                        <i class="ki-duotone ki-user fs-3 text-primary me-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                        New Order
                    </h3>
                </div>
                <div class="card-body pt-0" style="overflow-y: auto; max-height: calc(100vh - 280px);">
                    <!-- Customer Name -->
                    <div class="mb-4">
                        <label class="form-label fs-7 text-gray-600">Customer Name</label>
                        <input
                            type="text"
                            class="form-control form-control-solid form-control-sm"
                            placeholder="Enter name..."
                            v-model="newOrder.customer_name"
                            :class="{ 'bg-light-success border-success': customerFound }"
                        />
                    </div>

                    <!-- Phone Number -->
                    <div class="mb-4">
                        <label class="form-label fs-7 text-gray-600">Phone Number</label>
                        <div class="position-relative">
                            <input
                                type="text"
                                class="form-control form-control-solid form-control-sm"
                                placeholder="Search by phone or name..."
                                v-model="newOrder.customer_phone"
                                @input="searchCustomer"
                            />
                            <span v-if="searchingCustomer" class="position-absolute top-50 end-0 translate-middle-y me-3">
                                <span class="spinner-border spinner-border-sm text-primary"></span>
                            </span>
                            <!-- Customer Suggestions -->
                            <div v-if="customerSuggestions.length > 0" class="position-absolute w-100 bg-white shadow rounded mt-1 z-index-dropdown">
                                <div
                                    v-for="customer in customerSuggestions"
                                    :key="customer.id"
                                    class="px-3 py-2 cursor-pointer hover-bg-light-primary border-bottom"
                                    @click="selectCustomer(customer)"
                                >
                                    <span class="fw-semibold fs-7">{{ customer.name }}</span>
                                    <span class="text-muted fs-8 ms-2">{{ customer.phone }}</span>
                                </div>
                            </div>
                        </div>
                        <span v-if="customerFound" class="text-success fs-8 mt-1">
                            <i class="ki-duotone ki-check fs-8 text-success"></i> Existing customer
                        </span>
                    </div>

                    <!-- Add Items - 2 Column Grid -->
                    <div class="mb-4">
                        <label class="form-label fs-7 text-gray-600">Add Items</label>
                        <div v-if="!newOrder.service_id" class="alert alert-info py-2 fs-8">
                            <i class="bi bi-info-circle me-1"></i>
                            Select a service first to see prices
                        </div>
                        <div class="row g-2">
                            <div v-for="item in items" :key="item.id" class="col-6">
                                <button
                                    type="button"
                                    class="btn btn-sm w-100 text-start"
                                    :class="isItemSelected(item.id) ? 'btn-primary' : 'btn-light'"
                                    @click="toggleItem(item)"
                                >
                                    <i class="ki-duotone ki-plus fs-7 me-1" v-if="!isItemSelected(item.id)"></i>
                                    <span class="fs-8">{{ item.name }} ({{ formatCurrency(getItemDisplayPrice(item)) }}{{ isWeightBasedService ? '/lb' : '' }})</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Selected Items with Quantity or Weight -->
                    <div v-if="newOrder.items.length > 0" class="mb-4">
                        <div class="d-flex flex-column gap-2">
                            <div
                                v-for="(orderItem, index) in newOrder.items"
                                :key="index"
                                class="d-flex align-items-center justify-content-between bg-light-primary rounded p-2"
                            >
                                <span class="text-gray-800 fs-7 fw-semibold">{{ getItemName(orderItem.item_id) }}</span>
                                <!-- Weight input for weight-based services -->
                                <div v-if="isWeightBasedService" class="d-flex align-items-center gap-2">
                                    <input
                                        type="number"
                                        class="form-control form-control-sm form-control-solid"
                                        style="width: 70px;"
                                        v-model.number="orderItem.weight"
                                        step="0.1"
                                        min="0.1"
                                        placeholder="lbs"
                                    />
                                    <span class="text-muted fs-8">lb</span>
                                    <button
                                        class="btn btn-sm btn-icon btn-light-danger"
                                        style="width: 28px; height: 28px;"
                                        @click="newOrder.items.splice(index, 1)"
                                    >
                                        <i class="bi bi-x fs-4"></i>
                                    </button>
                                </div>
                                <!-- Quantity stepper for piece-based services -->
                                <div v-else class="d-flex align-items-center gap-2">
                                    <button
                                        class="btn btn-sm btn-icon btn-light-danger"
                                        style="width: 28px; height: 28px;"
                                        @click="decreaseQuantity(index)"
                                    >
                                        <i class="bi bi-dash fs-4"></i>
                                    </button>
                                    <span class="fw-bold fs-6 w-25px text-center">{{ orderItem.quantity }}</span>
                                    <button
                                        class="btn btn-sm btn-icon btn-light-success"
                                        style="width: 28px; height: 28px;"
                                        @click="increaseQuantity(index)"
                                    >
                                        <i class="bi bi-plus fs-4"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Service Type -->
                    <div class="mb-4">
                        <label class="form-label fs-7 text-gray-600">Service Type</label>
                        <select class="form-select form-select-solid form-select-sm" v-model="newOrder.service_id">
                            <option value="">Select service...</option>
                            <option v-for="service in services" :key="service.id" :value="service.id">
                                {{ service.name }}
                            </option>
                        </select>
                    </div>

                    <!-- Totals -->
                    <div class="bg-light rounded p-3 mb-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-gray-600 fs-7">Subtotal</span>
                            <span class="fw-semibold fs-7">{{ formatCurrency(orderSubtotal) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-gray-600 fs-7">Service ({{ selectedServiceName }})</span>
                            <span class="text-muted fs-7">1x</span>
                        </div>
                        <div class="d-flex justify-content-between border-top pt-2 mt-2">
                            <span class="fw-bold">Total Amount</span>
                            <span class="fw-bold text-primary fs-5">{{ formatCurrency(orderSubtotal) }}</span>
                        </div>
                    </div>

                    <!-- Create Button -->
                    <div class="d-flex gap-2">
                        <button
                            class="btn btn-primary btn-sm flex-grow-1"
                            :disabled="!canCreateOrder || creatingOrder"
                            @click="createOrder"
                        >
                            <i class="ki-duotone ki-check-circle fs-5 me-1" v-if="!creatingOrder">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                            <span class="spinner-border spinner-border-sm me-1" v-if="creatingOrder"></span>
                            Create Order
                        </button>
                        <button class="btn btn-sm btn-icon btn-light" title="Print">
                            <FormIcon icon="feather:printer" width="20" height="20" />
                        </button>
                    </div>
                </div>
            </div>

            <!-- Pending Column - Orange (Status ID: 2) -->
            <div class="card card-flush border-top-0" style="flex: 1; min-width: 200px;">
                <div class="card-header py-3 rounded-top" style="background-color: #f59e0b;">
                    <h3 class="card-title fs-6 fw-bold text-white">
                        <i class="ki-duotone ki-time fs-4 text-white me-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                        Pending
                    </h3>
                    <div class="card-toolbar">
                        <span class="badge badge-circle badge-light fw-bold">{{ filteredOrdersByStatus(2).length }}</span>
                    </div>
                </div>
                <div class="card-body py-3 bg-light-warning" style="overflow-y: auto; max-height: calc(100vh - 280px);">
                    <div
                        v-for="order in filteredOrdersByStatus(2)"
                        :key="order.id"
                        class="card card-flush shadow-sm mb-3 cursor-pointer"
                        @click="openOrderModal(order.id)"
                    >
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <span class="fs-7 fw-bold text-gray-900">#{{ order.order_number }}</span>
                                <span class="badge badge-light-info fs-8">{{ order.service_name }}</span>
                            </div>
                            <h4 class="fs-7 fw-semibold text-gray-800 mb-1">{{ order.customer_name }}</h4>
                            <p class="text-muted fs-8 mb-2">{{ order.item_summary }}</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fs-6 fw-bold text-primary">{{ formatCurrency(order.total_amount) }}</span>
                                <button
                                    class="btn btn-sm text-white"
                                    style="background-color: #f59e0b;"
                                    @click.stop="moveToStatus(order.id, 3)"
                                >
                                    Start Washing
                                    <i class="bi bi-arrow-right ms-1"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div v-if="filteredOrdersByStatus(2).length === 0" class="text-center text-muted py-5 fs-7">
                        No pending orders
                    </div>
                </div>
            </div>

            <!-- Washing Column - Blue (Status ID: 3) -->
            <div class="card card-flush border-top-0" style="flex: 1; min-width: 200px;">
                <div class="card-header py-3 rounded-top" style="background-color: #3b82f6;">
                    <h3 class="card-title fs-6 fw-bold text-white">
                        <i class="ki-duotone ki-drop fs-4 text-white me-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                        Washing
                    </h3>
                    <div class="card-toolbar">
                        <span class="badge badge-circle badge-light fw-bold">{{ filteredOrdersByStatus(3).length }}</span>
                    </div>
                </div>
                <div class="card-body py-3 bg-light-info" style="overflow-y: auto; max-height: calc(100vh - 280px);">
                    <div
                        v-for="order in filteredOrdersByStatus(3)"
                        :key="order.id"
                        class="card card-flush shadow-sm mb-3 cursor-pointer"
                        @click="openOrderModal(order.id)"
                    >
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <span class="fs-7 fw-bold text-gray-900">#{{ order.order_number }}</span>
                                <span class="badge badge-light-info fs-8">{{ order.service_name }}</span>
                            </div>
                            <h4 class="fs-7 fw-semibold text-gray-800 mb-1">{{ order.customer_name }}</h4>
                            <p class="text-muted fs-8 mb-2">{{ order.item_summary }}</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fs-6 fw-bold text-primary">{{ formatCurrency(order.total_amount) }}</span>
                                <button
                                    class="btn btn-sm btn-info"
                                    @click.stop="moveToStatus(order.id, 4)"
                                >
                                    Move to Drying
                                    <i class="bi bi-arrow-right ms-1"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div v-if="filteredOrdersByStatus(3).length === 0" class="text-center text-muted py-5 fs-7">
                        No orders in washing
                    </div>
                </div>
            </div>

            <!-- Drying Column - Purple (Status ID: 4) -->
            <div class="card card-flush border-top-0" style="flex: 1; min-width: 200px;">
                <div class="card-header py-3 rounded-top" style="background-color: #8b5cf6;">
                    <h3 class="card-title fs-6 fw-bold text-white">
                        <i class="ki-duotone ki-cloud fs-4 text-white me-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                        Drying
                    </h3>
                    <div class="card-toolbar">
                        <span class="badge badge-circle badge-light fw-bold">{{ filteredOrdersByStatus(4).length }}</span>
                    </div>
                </div>
                <div class="card-body py-3" style="background-color: #f3e8ff; overflow-y: auto; max-height: calc(100vh - 280px);">
                    <div
                        v-for="order in filteredOrdersByStatus(4)"
                        :key="order.id"
                        class="card card-flush shadow-sm mb-3 cursor-pointer"
                        @click="openOrderModal(order.id)"
                    >
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <span class="fs-7 fw-bold text-gray-900">#{{ order.order_number }}</span>
                                <span class="badge badge-light-info fs-8">{{ order.service_name }}</span>
                            </div>
                            <h4 class="fs-7 fw-semibold text-gray-800 mb-1">{{ order.customer_name }}</h4>
                            <p class="text-muted fs-8 mb-2">{{ order.item_summary }}</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fs-6 fw-bold text-primary">{{ formatCurrency(order.total_amount) }}</span>
                                <button
                                    class="btn btn-sm text-white"
                                    style="background-color: #8b5cf6;"
                                    @click.stop="moveToStatus(order.id, 5)"
                                >
                                    Mark as Ready
                                    <i class="bi bi-arrow-right ms-1"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div v-if="filteredOrdersByStatus(4).length === 0" class="text-center text-muted py-5 fs-7">
                        No orders in drying
                    </div>
                </div>
            </div>

            <!-- Pickup & Pay Column - Combined Ready + Payment (Status ID: 5) -->
            <div class="card card-flush border-top-0" style="flex: 1.2; min-width: 280px;">
                <div class="card-header py-3 rounded-top" style="background-color: #22c55e;">
                    <h3 class="card-title fs-6 fw-bold text-white">
                        <i class="ki-duotone ki-handcart fs-4 text-white me-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                        Pickup & Pay
                    </h3>
                    <div class="card-toolbar">
                        <span class="badge badge-circle badge-light fw-bold">{{ filteredOrdersByStatus(5).length }}</span>
                    </div>
                </div>
                <div class="card-body py-3 bg-light-success" style="overflow-y: auto; max-height: calc(100vh - 280px);">
                    <!-- Order Cards -->
                    <div
                        v-for="order in filteredOrdersByStatus(5)"
                        :key="order.id"
                        class="card card-flush shadow-sm mb-3"
                    >
                        <div
                            class="card-body p-3 cursor-pointer"
                            :class="{ 'bg-light-primary': selectedOrderId === order.id }"
                            @click="selectOrderForPayment(order.id)"
                        >
                            <!-- Order Header -->
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <span class="fs-7 fw-bold text-gray-900">#{{ order.order_number }}</span>
                                    <span class="badge badge-light-info fs-8 ms-2">{{ order.service_name }}</span>
                                </div>
                                <span class="fs-5 fw-bold text-success">{{ formatCurrency(order.balance_amount) }}</span>
                            </div>
                            <!-- Customer Info -->
                            <h4 class="fs-7 fw-semibold text-gray-800 mb-0">{{ order.customer_name }}</h4>
                            <span class="text-muted fs-8">{{ order.customer_phone }}</span>
                            <!-- Items -->
                            <p class="text-muted fs-8 mb-0 mt-1">{{ order.item_summary }}</p>
                        </div>

                        <!-- Payment Form - Expands when selected -->
                        <div v-if="selectedOrderId === order.id && selectedOrderDetail" class="border-top p-3 bg-white">
                            <!-- Order Summary -->
                            <div class="bg-light rounded p-2 mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="fs-8 text-gray-600">Subtotal</span>
                                    <span class="fs-8">{{ formatCurrency(selectedOrderDetail.subtotal) }}</span>
                                </div>
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="fs-8 text-gray-600">Discount</span>
                                    <span class="fs-8">-{{ formatCurrency(selectedOrderDetail.discount_amount) }}</span>
                                </div>
                                <div v-if="payment.tip > 0" class="d-flex justify-content-between mb-1">
                                    <span class="fs-8 text-gray-600">Tip</span>
                                    <span class="fs-8 text-info">+{{ formatCurrency(payment.tip) }}</span>
                                </div>
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="fs-8 text-gray-600">Paid</span>
                                    <span class="fs-8 text-success">-{{ formatCurrency(selectedOrderDetail.paid_amount) }}</span>
                                </div>
                                <div class="d-flex justify-content-between border-top pt-2 mt-2">
                                    <span class="fs-7 fw-bold">Balance Due</span>
                                    <span class="fs-6 fw-bold text-primary">{{ formatCurrency(balanceWithTip) }}</span>
                                </div>
                            </div>

                            <!-- Tip -->
                            <div class="mb-3">
                                <label class="form-label fs-8 text-gray-600 mb-1">Add Tip</label>
                                <div class="d-flex gap-2 mb-2">
                                    <button
                                        v-for="pct in tipPresets"
                                        :key="pct"
                                        class="btn btn-sm flex-grow-1"
                                        :class="selectedTipPreset === pct ? 'btn-info' : 'btn-light'"
                                        @click="applyTipPreset(pct)"
                                    >
                                        {{ pct }}%
                                    </button>
                                    <button
                                        class="btn btn-sm"
                                        :class="selectedTipPreset === 'custom' ? 'btn-info' : 'btn-light'"
                                        @click="selectedTipPreset = 'custom'"
                                    >
                                        Custom
                                    </button>
                                </div>
                                <input
                                    v-if="selectedTipPreset === 'custom'"
                                    type="number"
                                    class="form-control form-control-solid form-control-sm"
                                    v-model.number="payment.tip"
                                    step="0.01"
                                    min="0"
                                    placeholder="Enter tip amount"
                                />
                            </div>

                            <!-- Payment Method -->
                            <div class="mb-3">
                                <label class="form-label fs-8 text-gray-600 mb-1">Payment Method</label>
                                <div class="d-flex gap-2">
                                    <button
                                        v-for="method in paymentMethods"
                                        :key="method.value"
                                        class="btn btn-sm flex-grow-1"
                                        :class="payment.method === method.value ? 'btn-primary' : 'btn-light'"
                                        @click="payment.method = method.value"
                                    >
                                        {{ method.label }}
                                    </button>
                                </div>
                            </div>

                            <!-- Amount -->
                            <div class="mb-3">
                                <label class="form-label fs-8 text-gray-600 mb-1">Amount</label>
                                <input
                                    type="number"
                                    class="form-control form-control-solid form-control-sm"
                                    v-model="payment.amount"
                                />
                            </div>

                            <!-- Action Buttons -->
                            <div class="d-flex gap-2">
                                <button
                                    class="btn btn-light btn-sm"
                                    @click.stop="clearSelection"
                                >
                                    Cancel
                                </button>
                                <button
                                    class="btn btn-success btn-sm flex-grow-1"
                                    :disabled="!canProcessPayment || processingPayment"
                                    @click.stop="completePayment"
                                >
                                    <span class="spinner-border spinner-border-sm me-1" v-if="processingPayment"></span>
                                    Complete & Pay
                                </button>
                            </div>
                        </div>
                    </div>

                    <div v-if="filteredOrdersByStatus(5).length === 0" class="text-center text-muted py-5 fs-7">
                        No orders ready for pickup
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Details Modal - Minimal Design -->
        <div class="modal fade" id="orderDetailModal" tabindex="-1" ref="orderModalRef">
            <div class="modal-dialog modal-dialog-centered" :style="{ maxWidth: isEditMode ? '480px' : '380px' }">
                <div class="modal-content border-0 shadow-lg rounded-3" v-if="modalOrderDetail">
                    <!-- Header -->
                    <div class="d-flex align-items-center justify-content-between px-5 pt-5 pb-3">
                        <div class="d-flex align-items-center gap-3">
                            <span class="fs-2 fw-bold text-gray-900">#{{ modalOrderDetail.order_number }}</span>
                            <span class="badge rounded-pill px-3 py-2" :class="getStatusPillClass(modalOrderDetail.processing_status)">
                                {{ modalOrderDetail.processing_status }}
                            </span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <button
                                v-if="isOrderEditable && !isEditMode"
                                type="button"
                                class="btn btn-sm btn-light-primary"
                                @click="enterEditMode"
                            >
                                <i class="bi bi-pencil me-1"></i> Edit
                            </button>
                            <button type="button" class="btn btn-sm btn-icon btn-light btn-active-light-primary" @click="closeOrderModal">
                                <FormIcon icon="feather:x" width="20" height="20" />
                            </button>
                        </div>
                    </div>

                    <!-- View Mode Body -->
                    <div v-if="!isEditMode" class="px-5 pb-5">
                        <!-- Customer Info -->
                        <div class="mb-4">
                            <div class="d-flex align-items-center text-gray-800 mb-1">
                                <i class="ki-duotone ki-user fs-5 text-gray-500 me-2"><span class="path1"></span><span class="path2"></span></i>
                                <span class="fs-6 fw-semibold">{{ modalOrderDetail.customer?.name }}</span>
                            </div>
                            <div class="d-flex align-items-center text-gray-600">
                                <i class="ki-duotone ki-phone fs-5 text-gray-500 me-2"><span class="path1"></span><span class="path2"></span></i>
                                <span class="fs-7">{{ modalOrderDetail.customer?.phone }}</span>
                            </div>
                        </div>

                        <!-- Created Time -->
                        <div class="d-flex align-items-center text-gray-500 fs-7 mb-4">
                            <i class="ki-duotone ki-time fs-6 me-2"><span class="path1"></span><span class="path2"></span></i>
                            Created {{ getTimeAgo(modalOrderDetail.created_at) }}
                            <span class="text-gray-400 ms-2">({{ formatTimeOnly(modalOrderDetail.created_at) }})</span>
                        </div>

                        <div class="separator mb-4"></div>

                        <!-- Order Items -->
                        <div class="mb-4">
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <i class="ki-duotone ki-handcart fs-5 text-gray-600"><span class="path1"></span><span class="path2"></span></i>
                                <span class="fs-6 fw-semibold text-gray-800">Order Items</span>
                                <span class="badge badge-light-primary fs-8 ms-1">{{ modalOrderDetail.items[0]?.service_name }}</span>
                            </div>
                            <div class="d-flex flex-column gap-2">
                                <div v-for="item in modalOrderDetail.items" :key="item.id" class="d-flex justify-content-between">
                                    <span class="text-gray-700 fs-6">{{ item.item_name }} × {{ item.quantity }}</span>
                                    <span class="text-gray-800 fs-6 fw-semibold">{{ formatCurrency(item.total_price) }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Total Amount -->
                        <div class="d-flex justify-content-between align-items-center py-3 border-top">
                            <span class="fs-5 fw-bold text-gray-900">Total Amount</span>
                            <span class="fs-3 fw-bolder text-primary">{{ formatCurrency(modalOrderDetail.total_amount) }}</span>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex gap-3 mt-4">
                            <!-- Status IDs: 2=Pending, 3=Washing, 4=Drying, 5=Ready -->
                            <button
                                v-if="modalOrderDetail.processing_status_id === 2"
                                type="button"
                                class="btn btn-primary flex-grow-1"
                                @click="moveAndCloseModal(modalOrderDetail.id, 3)"
                            >
                                Start Washing <i class="bi bi-arrow-right ms-1"></i>
                            </button>
                            <button
                                v-else-if="modalOrderDetail.processing_status_id === 3"
                                type="button"
                                class="btn btn-info flex-grow-1"
                                @click="moveAndCloseModal(modalOrderDetail.id, 4)"
                            >
                                Move to Drying <i class="bi bi-arrow-right ms-1"></i>
                            </button>
                            <button
                                v-else-if="modalOrderDetail.processing_status_id === 4"
                                type="button"
                                class="btn flex-grow-1 text-white"
                                style="background-color: #8b5cf6;"
                                @click="moveAndCloseModal(modalOrderDetail.id, 5)"
                            >
                                Mark as Ready <i class="bi bi-arrow-right ms-1"></i>
                            </button>
                            <button
                                v-else-if="modalOrderDetail.processing_status_id === 5"
                                type="button"
                                class="btn btn-success flex-grow-1"
                                @click="closeOrderModal"
                            >
                                Ready for Pickup
                            </button>
                            <button
                                type="button"
                                class="btn btn-danger"
                                @click="confirmCancelOrder(modalOrderDetail.id)"
                            >
                                <i class="bi bi-x-circle me-1"></i> Cancel Order
                            </button>
                        </div>

                        <!-- Warning Text -->
                        <div class="text-center mt-3">
                            <span class="text-gray-500 fs-8">
                                <i class="bi bi-exclamation-triangle me-1"></i>
                                Canceling will remove this order permanently
                            </span>
                        </div>
                    </div>

                    <!-- Edit Mode Body -->
                    <div v-else class="px-5 pb-5">
                        <!-- Item Picker Grid -->
                        <div class="mb-4">
                            <label class="form-label fs-7 text-gray-600">Select Items</label>
                            <div class="row g-2">
                                <div v-for="item in items" :key="item.id" class="col-6">
                                    <button
                                        type="button"
                                        class="btn btn-sm w-100 text-start"
                                        :class="isEditItemSelected(item.id) ? 'btn-primary' : 'btn-light'"
                                        @click="toggleEditItem(item)"
                                    >
                                        <i class="ki-duotone ki-plus fs-7 me-1" v-if="!isEditItemSelected(item.id)"></i>
                                        <span class="fs-8">{{ item.name }} ({{ formatCurrency(getEditItemDisplayPrice(item)) }}{{ editServiceIsWeightBased ? '/lb' : '' }})</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Selected Items with Controls -->
                        <div v-if="editOrder.items.length > 0" class="mb-4">
                            <label class="form-label fs-7 text-gray-600">Selected Items</label>
                            <div class="d-flex flex-column gap-2">
                                <div
                                    v-for="(orderItem, index) in editOrder.items"
                                    :key="index"
                                    class="d-flex align-items-center justify-content-between bg-light-primary rounded p-2"
                                >
                                    <span class="text-gray-800 fs-7 fw-semibold">{{ getItemName(orderItem.item_id) }}</span>
                                    <!-- Weight input for weight-based services -->
                                    <div v-if="editServiceIsWeightBased" class="d-flex align-items-center gap-2">
                                        <input
                                            type="number"
                                            class="form-control form-control-sm form-control-solid"
                                            style="width: 70px;"
                                            v-model.number="orderItem.weight"
                                            step="0.1"
                                            min="0.1"
                                            placeholder="lbs"
                                        />
                                        <span class="text-muted fs-8">lb</span>
                                        <button
                                            class="btn btn-sm btn-icon btn-light-danger"
                                            style="width: 28px; height: 28px;"
                                            @click="editOrder.items.splice(index, 1)"
                                        >
                                            <i class="bi bi-x fs-4"></i>
                                        </button>
                                    </div>
                                    <!-- Quantity stepper for piece-based services -->
                                    <div v-else class="d-flex align-items-center gap-2">
                                        <button
                                            class="btn btn-sm btn-icon btn-light-danger"
                                            style="width: 28px; height: 28px;"
                                            @click="decreaseEditQuantity(index)"
                                        >
                                            <i class="bi bi-dash fs-4"></i>
                                        </button>
                                        <span class="fw-bold fs-6 w-25px text-center">{{ orderItem.quantity }}</span>
                                        <button
                                            class="btn btn-sm btn-icon btn-light-success"
                                            style="width: 28px; height: 28px;"
                                            @click="increaseEditQuantity(index)"
                                        >
                                            <i class="bi bi-plus fs-4"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="mb-4">
                            <label class="form-label fs-7 text-gray-600">Notes</label>
                            <textarea
                                class="form-control form-control-solid form-control-sm"
                                rows="2"
                                v-model="editOrder.notes"
                                placeholder="Order notes..."
                            ></textarea>
                        </div>

                        <!-- Urgent Toggle -->
                        <div class="form-check form-switch mb-4">
                            <input class="form-check-input" type="checkbox" id="editUrgentToggle" v-model="editOrder.urgent" />
                            <label class="form-check-label fs-7 text-gray-700" for="editUrgentToggle">Urgent Order</label>
                        </div>

                        <!-- Running Total -->
                        <div class="d-flex justify-content-between align-items-center py-3 border-top mb-4">
                            <span class="fs-5 fw-bold text-gray-900">Estimated Total</span>
                            <span class="fs-3 fw-bolder text-primary">{{ formatCurrency(editOrderSubtotal) }}</span>
                        </div>

                        <!-- Save / Cancel Buttons -->
                        <div class="d-flex gap-3">
                            <button
                                type="button"
                                class="btn btn-light flex-grow-1"
                                @click="cancelEditMode"
                                :disabled="savingOrder"
                            >
                                Cancel
                            </button>
                            <button
                                type="button"
                                class="btn btn-primary flex-grow-1"
                                :disabled="editOrder.items.length === 0 || savingOrder"
                                @click="saveOrderEdit"
                            >
                                <span class="spinner-border spinner-border-sm me-1" v-if="savingOrder"></span>
                                Save Changes
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order History Modal -->
        <div class="modal fade" id="orderHistoryModal" tabindex="-1" ref="historyModalRef">
            <div class="modal-dialog modal-dialog-centered modal-xl">
                <div class="modal-content border-0 shadow-lg rounded-3 overflow-hidden">
                    <!-- Header - Dark Gradient -->
                    <div class="px-6 py-5" style="background: linear-gradient(135deg, #1e3a5f 0%, #2c5282 100%);">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center gap-3">
                                <i class="ki-duotone ki-time fs-2x text-white">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                                <div>
                                    <h3 class="fs-2 fw-bold text-white mb-1">Order History</h3>
                                    <div class="d-flex gap-2">
                                        <span class="badge bg-white bg-opacity-20 text-white fs-8 px-3 py-1">
                                            {{ historyTotal }} orders
                                        </span>
                                        <span class="badge bg-white bg-opacity-20 text-white fs-8 px-3 py-1">
                                            {{ formatCurrency(historyRevenue) }} revenue
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-icon btn-active-color-white text-white" @click="closeHistoryModal">
                                <FormIcon icon="feather:x" width="22" height="22" />
                            </button>
                        </div>
                    </div>

                    <!-- Body -->
                    <div class="modal-body px-6 py-5">
                        <!-- Search Input - Solid Background -->
                        <div class="position-relative mb-4">
                            <i class="ki-duotone ki-magnifier fs-4 position-absolute top-50 translate-middle-y ms-4 text-gray-500">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                            <input
                                type="text"
                                class="form-control form-control-solid ps-12"
                                placeholder="Search by order #, customer name or phone..."
                                v-model="historySearch"
                                @input="searchHistory"
                            />
                        </div>

                        <!-- Summary Stat Cards -->
                        <div class="row g-3 mb-5">
                            <div class="col-4">
                                <div class="bg-light-primary rounded p-3 text-center">
                                    <div class="fs-3 fw-bolder text-primary">{{ historyTotal }}</div>
                                    <div class="text-gray-600 fs-8 fw-semibold">Total Orders</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="bg-light-success rounded p-3 text-center">
                                    <div class="fs-3 fw-bolder text-success">{{ formatCurrency(historyRevenue) }}</div>
                                    <div class="text-gray-600 fs-8 fw-semibold">Revenue</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="bg-light-info rounded p-3 text-center">
                                    <div class="fs-3 fw-bolder text-info">{{ formatCurrency(historyAvgOrder) }}</div>
                                    <div class="text-gray-600 fs-8 fw-semibold">Avg Order</div>
                                </div>
                            </div>
                        </div>

                        <!-- Table -->
                        <div class="table-responsive">
                            <table class="table align-middle gs-4 gy-4 mb-0">
                                <thead>
                                    <tr class="fw-semibold text-gray-600 fs-7 border-bottom border-gray-200">
                                        <th class="min-w-100px">Order #</th>
                                        <th class="min-w-140px">Customer</th>
                                        <th class="min-w-200px">Items</th>
                                        <th class="min-w-100px">Service</th>
                                        <th class="min-w-80px text-end">Total</th>
                                        <th class="min-w-100px">Payment</th>
                                        <th class="min-w-120px">Completed</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-if="historyLoading">
                                        <td colspan="7" class="text-center py-10">
                                            <span class="spinner-border spinner-border-sm text-primary me-2"></span>
                                            <span class="text-gray-600">Loading orders...</span>
                                        </td>
                                    </tr>
                                    <tr v-else-if="historyOrders.length === 0">
                                        <td colspan="7" class="text-center py-15">
                                            <i class="ki-duotone ki-document fs-3x text-gray-300 mb-3 d-block">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                            </i>
                                            <div class="text-gray-500 fs-6 fw-semibold">No completed orders found</div>
                                            <div class="text-gray-400 fs-7 mt-1">Try adjusting your search criteria</div>
                                        </td>
                                    </tr>
                                    <tr
                                        v-for="order in historyOrders"
                                        :key="order.id"
                                        class="history-row"
                                        :class="{ 'cancelled': order.processing_status_id === 1 }"
                                    >
                                        <td>
                                            <span class="bg-light rounded-pill px-2 py-1 fw-bold text-gray-800 fs-7">#{{ order.order_number }}</span>
                                            <span v-if="order.processing_status_id === 1" class="badge badge-light-danger fs-9 ms-1">Cancelled</span>
                                        </td>
                                        <td>
                                            <div class="fw-semibold text-gray-800 fs-7">{{ order.customer_name }}</div>
                                            <div class="text-muted fs-8">{{ order.customer_phone }}</div>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-wrap gap-1">
                                                <span
                                                    v-for="(item, idx) in splitItemSummary(order.item_summary).slice(0, 3)"
                                                    :key="idx"
                                                    class="badge badge-light-secondary fs-9 fw-normal"
                                                >{{ item }}</span>
                                                <span
                                                    v-if="splitItemSummary(order.item_summary).length > 3"
                                                    class="badge badge-light fs-9 fw-semibold text-gray-600"
                                                >+{{ splitItemSummary(order.item_summary).length - 3 }} more</span>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge badge-light-info fs-8">{{ order.service_name }}</span>
                                        </td>
                                        <td class="text-end">
                                            <span
                                                class="fs-6 fw-bold"
                                                :class="order.processing_status_id === 1 ? 'text-decoration-line-through text-gray-500' : 'text-gray-900'"
                                            >{{ formatCurrency(order.total_amount) }}</span>
                                        </td>
                                        <td>
                                            <span class="badge fs-8 d-inline-flex align-items-center gap-1" :class="getPaymentMethodBadge(order.payment_method)">
                                                <i :class="getPaymentMethodIcon(order.payment_method)" class="fs-8"></i>
                                                {{ order.payment_method_label }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="fw-semibold text-gray-800 fs-7">{{ formatHistoryDate(order.completed_at).date }}</div>
                                            <div class="text-muted fs-8">{{ formatHistoryDate(order.completed_at).time }}</div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Footer - Prominent Summary Bar -->
                        <div class="bg-light-success rounded p-4 mt-5 d-flex justify-content-between align-items-center">
                            <span class="text-gray-700 fs-7 fw-semibold">
                                Showing {{ historyOrders.length }} of {{ historyTotal }} completed orders
                            </span>
                            <div class="d-flex align-items-center gap-2">
                                <span class="text-gray-600 fs-7">Total Revenue:</span>
                                <span class="fs-3 fw-bolder text-success">{{ formatCurrency(historyRevenue) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount, watch } from "vue";
import { usePosStore } from "./PosStore";
import ApiService from "@/core/services/ApiService";
import Swal from "sweetalert2";
import { Modal } from "bootstrap";
import { formatCurrency } from "@utility@/currency";
import { $toastSuccess, $toastError } from "@/core/helpers/utility";
import FormIcon from "@common@/components/form/FormIcon.vue";

const posStore = usePosStore();

// State
const newOrder = ref({
    customer_name: "",
    customer_phone: "",
    service_id: "",
    items: [],
    urgent: false,
    notes: "",
});
const creatingOrder = ref(false);
const processingPayment = ref(false);
const selectedOrderDetail = ref(null);
const payment = ref({
    method: "cash",
    amount: 0,
    tip: 0,
});
const selectedTipPreset = ref(null);
const tipPresets = [10, 15, 20];
const searchQuery = ref("");
const searchingCustomer = ref(false);
const customerSuggestions = ref([]);
const customerFound = ref(false);
let customerSearchTimeout = null;

// Timer refs for cleanup
let refreshIntervalId = null;

// Modal state
const showOrderModal = ref(false);
const modalOrderDetail = ref(null);
const orderModalRef = ref(null);
let bootstrapModal = null;

// Edit mode state
const isEditMode = ref(false);
const editOrder = ref({ items: [], urgent: false, notes: "" });
const savingOrder = ref(false);

// History modal state
const historyModalRef = ref(null);
let historyModal = null;
const historyOrders = ref([]);
const historySearch = ref("");
const historyLoading = ref(false);
const historyTotal = ref(0);
const historyRevenue = ref(0);
let historySearchTimeout = null;

const paymentMethods = [
    { value: "cash", label: "Cash" },
    { value: "card", label: "Card" },
    { value: "apple_pay", label: "Apple Pay" },
    { value: "google_pay", label: "Google Pay" },
];

// Computed
const statistics = computed(() => posStore.statistics);
const items = computed(() => posStore.items);
const services = computed(() => posStore.services);
const selectedOrderId = computed(() => posStore.selectedOrderId);

const todayDate = computed(() => {
    const options = { weekday: "long", day: "numeric", month: "short" };
    return new Date().toLocaleDateString("en-US", options);
});

// Get price for an item based on selected service
function getItemPrice(itemId) {
    const item = items.value.find((i) => i.id === itemId);
    if (!item) return 0;

    // If a service is selected, try to find service-specific price
    if (newOrder.value.service_id && item.service_prices) {
        const servicePrice = item.service_prices.find(
            (sp) => sp.service_id === parseInt(newOrder.value.service_id)
        );
        if (servicePrice) {
            if (isWeightBasedService.value && servicePrice.price_per_pound !== null) {
                return parseFloat(servicePrice.price_per_pound);
            }
            if (servicePrice.price !== null) {
                return parseFloat(servicePrice.price);
            }
        }
    }

    if (isWeightBasedService.value) {
        const service = services.value.find((s) => s.id === parseInt(newOrder.value.service_id));
        if (service && service.price_per_pound) {
            return parseFloat(service.price_per_pound);
        }
    }

    // Fall back to default item price
    return parseFloat(item.price) || 0;
}

// Get display price for item chip (show service price or default)
function getItemDisplayPrice(item) {
    if (newOrder.value.service_id && item.service_prices) {
        const servicePrice = item.service_prices.find(
            (sp) => sp.service_id === parseInt(newOrder.value.service_id)
        );
        if (servicePrice) {
            if (isWeightBasedService.value && servicePrice.price_per_pound !== null) {
                return parseFloat(servicePrice.price_per_pound);
            }
            if (servicePrice.price !== null) {
                return parseFloat(servicePrice.price);
            }
        }
    }
    if (isWeightBasedService.value) {
        const service = services.value.find((s) => s.id === parseInt(newOrder.value.service_id));
        if (service && service.price_per_pound) {
            return parseFloat(service.price_per_pound);
        }
    }
    return parseFloat(item.price) || 0;
}

const orderSubtotal = computed(() => {
    return newOrder.value.items.reduce((sum, orderItem) => {
        const price = getItemPrice(orderItem.item_id);
        if (isWeightBasedService.value) {
            return sum + price * (orderItem.weight || 0);
        }
        return sum + price * orderItem.quantity;
    }, 0);
});

const selectedServiceName = computed(() => {
    const service = services.value.find((s) => s.id === newOrder.value.service_id);
    return service?.name || "-";
});

const isWeightBasedService = computed(() => {
    const service = services.value.find((s) => s.id === parseInt(newOrder.value.service_id));
    return service && (service.pricing_type === 'weight' || service.pricing_type === 'both');
});

const balanceWithTip = computed(() => {
    if (!selectedOrderDetail.value) return 0;
    return selectedOrderDetail.value.balance_amount + (payment.value.tip || 0) - (selectedOrderDetail.value.tip_amount || 0);
});

const canCreateOrder = computed(() => {
    return (
        newOrder.value.customer_name.trim() &&
        newOrder.value.customer_phone.trim() &&
        newOrder.value.service_id &&
        newOrder.value.items.length > 0
    );
});

const canProcessPayment = computed(() => {
    return payment.value.method && payment.value.amount > 0;
});

const historyAvgOrder = computed(() => {
    if (!historyTotal.value || !historyRevenue.value) return 0;
    return historyRevenue.value / historyTotal.value;
});

// Methods
function getOrdersByStatusId(statusId) {
    return posStore.getOrdersByStatusId(statusId);
}

function filteredOrdersByStatus(statusId) {
    const orders = getOrdersByStatusId(statusId);
    if (!searchQuery.value.trim()) return orders;

    const query = searchQuery.value.toLowerCase();
    return orders.filter((order) =>
        order.order_number?.toLowerCase().includes(query) ||
        order.customer_name?.toLowerCase().includes(query) ||
        order.customer_phone?.includes(query)
    );
}

// Modal functions
async function openOrderModal(orderId) {
    try {
        modalOrderDetail.value = await posStore.fetchOrderDetail(orderId);
        // Use Bootstrap modal
        if (orderModalRef.value) {
            bootstrapModal = new Modal(orderModalRef.value);
            bootstrapModal.show();
        }
    } catch (error) {
        console.error("Failed to fetch order detail:", error);
        $toastError("Failed to load order details");
    }
}

function closeOrderModal() {
    if (bootstrapModal) {
        bootstrapModal.hide();
    }
    modalOrderDetail.value = null;
    isEditMode.value = false;
}

// Edit mode computeds
const isOrderEditable = computed(() => {
    return modalOrderDetail.value?.is_editable === true;
});

const editServiceIsWeightBased = computed(() => {
    if (!modalOrderDetail.value?.items?.length) return false;
    const serviceId = modalOrderDetail.value.items[0]?.service_id;
    const service = services.value.find((s) => s.id === serviceId);
    return service && (service.pricing_type === 'weight' || service.pricing_type === 'both');
});

const editOrderSubtotal = computed(() => {
    if (!modalOrderDetail.value?.items?.length) return 0;
    const serviceId = modalOrderDetail.value.items[0]?.service_id;
    return editOrder.value.items.reduce((sum, orderItem) => {
        const item = items.value.find((i) => i.id === orderItem.item_id);
        if (!item) return sum;
        if (editServiceIsWeightBased.value) {
            let price = 0;
            if (item.service_prices) {
                const sp = item.service_prices.find((sp) => sp.service_id === serviceId);
                if (sp && sp.price_per_pound !== null) price = parseFloat(sp.price_per_pound);
            }
            if (!price) {
                const service = services.value.find((s) => s.id === serviceId);
                price = parseFloat(service?.price_per_pound) || 0;
            }
            return sum + price * (orderItem.weight || 0);
        } else {
            let price = 0;
            if (item.service_prices) {
                const sp = item.service_prices.find((sp) => sp.service_id === serviceId);
                if (sp && sp.price !== null) price = parseFloat(sp.price);
            }
            if (!price) price = parseFloat(item.price) || 0;
            return sum + price * orderItem.quantity;
        }
    }, 0);
});

// Edit mode functions
function enterEditMode() {
    editOrder.value = {
        items: modalOrderDetail.value.items.map((item) => ({
            item_id: item.item_id,
            quantity: item.quantity,
            weight: item.weight ? parseFloat(item.weight) : null,
            weight_unit: item.weight_unit || 'lb',
            notes: item.notes,
        })),
        urgent: modalOrderDetail.value.urgent,
        notes: modalOrderDetail.value.notes || "",
    };
    isEditMode.value = true;
}

function cancelEditMode() {
    isEditMode.value = false;
    editOrder.value = { items: [], urgent: false, notes: "" };
}

function isEditItemSelected(itemId) {
    return editOrder.value.items.some((i) => i.item_id === itemId);
}

function toggleEditItem(item) {
    const index = editOrder.value.items.findIndex((i) => i.item_id === item.id);
    if (index === -1) {
        editOrder.value.items.push({ item_id: item.id, quantity: 1, weight: null, weight_unit: 'lb', notes: null });
    } else {
        editOrder.value.items.splice(index, 1);
    }
}

function getEditItemDisplayPrice(item) {
    if (!modalOrderDetail.value?.items?.length) return parseFloat(item.price) || 0;
    const serviceId = modalOrderDetail.value.items[0]?.service_id;
    if (serviceId && item.service_prices) {
        const sp = item.service_prices.find((sp) => sp.service_id === serviceId);
        if (sp) {
            if (editServiceIsWeightBased.value && sp.price_per_pound !== null) return parseFloat(sp.price_per_pound);
            if (sp.price !== null) return parseFloat(sp.price);
        }
    }
    if (editServiceIsWeightBased.value) {
        const service = services.value.find((s) => s.id === serviceId);
        if (service && service.price_per_pound) return parseFloat(service.price_per_pound);
    }
    return parseFloat(item.price) || 0;
}

function increaseEditQuantity(index) {
    editOrder.value.items[index].quantity++;
}

function decreaseEditQuantity(index) {
    if (editOrder.value.items[index].quantity > 1) {
        editOrder.value.items[index].quantity--;
    } else {
        editOrder.value.items.splice(index, 1);
    }
}

async function saveOrderEdit() {
    if (editOrder.value.items.length === 0 || savingOrder.value) return;
    savingOrder.value = true;
    try {
        const payload = {
            items: editOrder.value.items.map((item) => ({
                item_id: item.item_id,
                quantity: item.quantity,
                weight: editServiceIsWeightBased.value ? item.weight : undefined,
                weight_unit: editServiceIsWeightBased.value ? (item.weight_unit || 'lb') : undefined,
                notes: item.notes,
            })),
            urgent: editOrder.value.urgent,
            notes: editOrder.value.notes,
        };
        await posStore.updateOrder(modalOrderDetail.value.id, payload);
        // Refresh modal detail
        modalOrderDetail.value = await posStore.fetchOrderDetail(modalOrderDetail.value.id);
        isEditMode.value = false;
        $toastSuccess("Order updated successfully!");
    } catch (error) {
        $toastError(error.response?.data?.message || "Failed to update order");
    } finally {
        savingOrder.value = false;
    }
}

async function moveAndCloseModal(orderId, newStatusId) {
    try {
        await posStore.updateOrderStatus(orderId, newStatusId);
        closeOrderModal();
        $toastSuccess("Order status updated!");
    } catch (error) {
        $toastError("Failed to update status");
    }
}


function getTimeAgo(dateString) {
    if (!dateString) return '';
    const date = new Date(dateString);
    const now = new Date();
    const seconds = Math.floor((now - date) / 1000);
    const minutes = Math.floor(seconds / 60);
    const hours = Math.floor(minutes / 60);
    const days = Math.floor(hours / 24);

    if (days > 0) return `about ${days} days ago`;
    if (hours > 0) return `about ${hours} hours ago`;
    if (minutes > 0) return `about ${minutes} minutes ago`;
    return 'just now';
}

function formatTimeOnly(dateString) {
    if (!dateString) return '';
    return new Date(dateString).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
}

function confirmCancelOrder(orderId) {
    Swal.fire({
        title: "Cancel Order?",
        text: "Are you sure you want to cancel this order? This action cannot be undone.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes, cancel it!",
        cancelButtonText: "No, keep it",
        customClass: {
            confirmButton: "btn btn-danger",
            cancelButton: "btn btn-active-light"
        }
    }).then(async (result) => {
        if (result.isConfirmed) {
            try {
                await posStore.cancelOrder(orderId);
                closeOrderModal();
                Swal.fire("Cancelled!", "The order has been cancelled.", "success");
            } catch (error) {
                Swal.fire("Error!", "Failed to cancel order.", "error");
            }
        }
    });
}

function getStatusBadgeClass(status) {
    const statusLower = status?.toLowerCase() || '';
    if (statusLower.includes('pending')) return 'badge-light-warning text-warning';
    if (statusLower.includes('washing')) return 'badge-light-info text-info';
    if (statusLower.includes('drying')) return 'badge-light-primary text-primary';
    if (statusLower.includes('ready')) return 'badge-light-success text-success';
    if (statusLower.includes('cancelled')) return 'badge-light-danger text-danger';
    return 'badge-light-secondary';
}

function getStatusPillClass(status) {
    const statusLower = status?.toLowerCase() || '';
    if (statusLower.includes('pending')) return 'bg-warning text-dark';
    if (statusLower.includes('washing')) return 'bg-info text-white';
    if (statusLower.includes('drying')) return 'bg-primary text-white';
    if (statusLower.includes('ready')) return 'bg-success text-white';
    if (statusLower.includes('cancelled')) return 'bg-danger text-white';
    return 'bg-secondary text-white';
}

async function searchCustomer() {
    const query = newOrder.value.customer_phone.trim();
    customerFound.value = false;

    if (query.length < 3) {
        customerSuggestions.value = [];
        return;
    }

    // Debounce the search
    if (customerSearchTimeout) clearTimeout(customerSearchTimeout);

    customerSearchTimeout = setTimeout(async () => {
        searchingCustomer.value = true;
        try {
            const response = await ApiService.get(`pos/customers/search?q=${encodeURIComponent(query)}`);
            if (response.data.success && response.data.data.length > 0) {
                customerSuggestions.value = response.data.data;
            } else {
                customerSuggestions.value = [];
            }
        } catch (error) {
            customerSuggestions.value = [];
        } finally {
            searchingCustomer.value = false;
        }
    }, 300);
}

function selectCustomer(customer) {
    newOrder.value.customer_name = customer.name;
    newOrder.value.customer_phone = customer.phone;
    customerFound.value = true;
    customerSuggestions.value = [];
}


function isItemSelected(itemId) {
    return newOrder.value.items.some((i) => i.item_id === itemId);
}

function toggleItem(item) {
    const index = newOrder.value.items.findIndex((i) => i.item_id === item.id);
    if (index === -1) {
        newOrder.value.items.push({ item_id: item.id, quantity: 1, weight: null });
    } else {
        newOrder.value.items.splice(index, 1);
    }
}

function applyTipPreset(pct) {
    if (!selectedOrderDetail.value) return;
    selectedTipPreset.value = pct;
    const subtotal = selectedOrderDetail.value.subtotal - selectedOrderDetail.value.discount_amount;
    payment.value.tip = parseFloat((subtotal * pct / 100).toFixed(2));
    payment.value.amount = balanceWithTip.value;
}

function getItemName(itemId) {
    return items.value.find((i) => i.id === itemId)?.name || "";
}

function increaseQuantity(index) {
    newOrder.value.items[index].quantity++;
}

function decreaseQuantity(index) {
    if (newOrder.value.items[index].quantity > 1) {
        newOrder.value.items[index].quantity--;
    } else {
        newOrder.value.items.splice(index, 1);
    }
}

async function createOrder() {
    if (!canCreateOrder.value) return;

    creatingOrder.value = true;
    try {
        const orderItems = newOrder.value.items.map((item) => ({
            item_id: item.item_id,
            quantity: item.quantity,
            weight: isWeightBasedService.value ? item.weight : undefined,
            weight_unit: isWeightBasedService.value ? 'lb' : undefined,
            notes: item.notes,
        }));
        await posStore.createOrder({
            customer_name: newOrder.value.customer_name,
            customer_phone: newOrder.value.customer_phone,
            service_id: newOrder.value.service_id,
            items: orderItems,
            urgent: newOrder.value.urgent,
            notes: newOrder.value.notes,
        });

        // Reset form
        newOrder.value = {
            customer_name: "",
            customer_phone: "",
            service_id: "",
            items: [],
            urgent: false,
            notes: "",
        };
        customerFound.value = false;
        customerSuggestions.value = [];

        $toastSuccess("Order created successfully!");
    } catch (error) {
        $toastError(error.response?.data?.message || "Failed to create order");
    } finally {
        creatingOrder.value = false;
    }
}

async function moveToStatus(orderId, newStatusId) {
    try {
        await posStore.updateOrderStatus(orderId, newStatusId);
        $toastSuccess("Order status updated!");
    } catch (error) {
        $toastError("Failed to update status");
    }
}

async function selectOrderForPayment(orderId) {
    posStore.selectOrder(orderId);
    try {
        selectedOrderDetail.value = await posStore.fetchOrderDetail(orderId);
        payment.value.amount = selectedOrderDetail.value.balance_amount;
        payment.value.tip = selectedOrderDetail.value.tip_amount || 0;
        selectedTipPreset.value = null;
    } catch (error) {
        console.error("Failed to fetch order detail:", error);
    }
}

function clearSelection() {
    posStore.clearSelection();
    selectedOrderDetail.value = null;
}

async function completePayment() {
    if (!canProcessPayment.value || !selectedOrderId.value) return;

    processingPayment.value = true;
    try {
        await posStore.processPayment(selectedOrderId.value, {
            amount: payment.value.amount,
            payment_method: payment.value.method,
            tip_amount: payment.value.tip || 0,
        });

        selectedOrderDetail.value = null;
        payment.value = { method: "cash", amount: 0, tip: 0 };
        selectedTipPreset.value = null;

        $toastSuccess("Payment complete! Order delivered.");
    } catch (error) {
        $toastError(error.response?.data?.message || "Failed to process payment");
    } finally {
        processingPayment.value = false;
    }
}

function getPaymentBadgeClass(status) {
    const classes = {
        unpaid: "badge-light-danger",
        partial: "badge-light-warning",
        paid: "badge-light-success",
    };
    return classes[status] || "badge-light";
}

function getPaymentMethodBadge(method) {
    const badges = {
        cash: "badge-light-success",
        card: "badge-light-info",
        apple_pay: "badge-light-dark",
        google_pay: "badge-light-primary",
        other: "badge-light-secondary",
    };
    return badges[method] || "badge-light";
}

function formatCompletedDate(dateString) {
    if (!dateString) return '-';
    const date = new Date(dateString);
    const day = date.getDate();
    const month = date.toLocaleString('en-US', { month: 'short' });
    const hours = date.getHours().toString().padStart(2, '0');
    const minutes = date.getMinutes().toString().padStart(2, '0');
    return `${day} ${month}, ${hours}:${minutes}`;
}

function splitItemSummary(summary) {
    if (!summary) return [];
    return summary.split(',').map((s) => s.trim()).filter(Boolean);
}

function formatHistoryDate(dateString) {
    if (!dateString) return { date: '-', time: '' };
    const date = new Date(dateString);
    const day = date.getDate();
    const month = date.toLocaleString('en-US', { month: 'short' });
    const hours = date.getHours().toString().padStart(2, '0');
    const minutes = date.getMinutes().toString().padStart(2, '0');
    return { date: `${day} ${month}`, time: `${hours}:${minutes}` };
}

function getPaymentMethodIcon(method) {
    const icons = {
        cash: 'bi bi-cash-stack',
        card: 'bi bi-credit-card',
        apple_pay: 'bi bi-phone',
        google_pay: 'bi bi-phone',
    };
    return icons[method] || 'bi bi-three-dots';
}

// History Modal Functions
async function openHistoryModal() {
    if (historyModalRef.value) {
        historyModal = new Modal(historyModalRef.value);
        historyModal.show();
    }
    await fetchOrderHistory();
}

function closeHistoryModal() {
    if (historyModal) {
        historyModal.hide();
    }
    historySearch.value = "";
}

async function fetchOrderHistory(search = "") {
    historyLoading.value = true;
    try {
        const params = new URLSearchParams();
        if (search) params.append('search', search);

        const response = await ApiService.get(`pos/history?${params.toString()}`);
        if (response.data.success) {
            historyOrders.value = response.data.data.orders || [];
            historyTotal.value = response.data.data.total || 0;
            historyRevenue.value = response.data.data.revenue || 0;
        }
    } catch (error) {
        console.error("Failed to fetch order history:", error);
        historyOrders.value = [];
    } finally {
        historyLoading.value = false;
    }
}

function searchHistory() {
    if (historySearchTimeout) clearTimeout(historySearchTimeout);
    historySearchTimeout = setTimeout(() => {
        fetchOrderHistory(historySearch.value);
    }, 300);
}

// Lifecycle
onMounted(() => {
    posStore.fetchBoardData();

    // Auto-refresh every 30 seconds
    refreshIntervalId = setInterval(() => {
        posStore.refreshStatistics();
    }, 30000);
});

onBeforeUnmount(() => {
    // Clear auto-refresh interval
    if (refreshIntervalId) {
        clearInterval(refreshIntervalId);
        refreshIntervalId = null;
    }

    // Clear search debounce timeout
    if (historySearchTimeout) {
        clearTimeout(historySearchTimeout);
    }

    // Dispose Bootstrap modals to prevent memory leaks
    if (bootstrapModal) {
        bootstrapModal.dispose();
        bootstrapModal = null;
    }
    if (historyModal) {
        historyModal.dispose();
        historyModal = null;
    }
});

// Watch for order selection changes
watch(selectedOrderId, (newId) => {
    if (!newId) {
        selectedOrderDetail.value = null;
    }
});
</script>

<style scoped>
.cursor-pointer {
    cursor: pointer;
}
.cursor-pointer:hover {
    box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.1);
}
.hover-bg-light-primary:hover {
    background-color: #f1faff;
}
.z-index-dropdown {
    z-index: 1050;
}
.btn-xs {
    padding: 0.15rem 0.35rem;
    font-size: 0.75rem;
}
.history-row:nth-child(even) {
    background-color: #f9fafb;
}
.history-row:hover {
    background-color: #f1faff;
    transition: background-color 0.15s ease;
}
.history-row.cancelled {
    background-color: #fff5f5;
}
.history-row.cancelled:hover {
    background-color: #fee2e2;
}
</style>
