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
                    <div class="text-end">
                        <span class="text-white opacity-75 fs-7">Today</span>
                        <h3 class="fs-4 fw-bold text-white mb-0">{{ todayDate }}</h3>
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
                                    <span class="fs-8">{{ item.name }} ({{ formatCurrency(getItemDisplayPrice(item)) }})</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Selected Items with Quantity -->
                    <div v-if="newOrder.items.length > 0" class="mb-4">
                        <div class="d-flex flex-column gap-2">
                            <div
                                v-for="(orderItem, index) in newOrder.items"
                                :key="index"
                                class="d-flex align-items-center justify-content-between bg-light-primary rounded p-2"
                            >
                                <span class="text-gray-800 fs-7 fw-semibold">{{ getItemName(orderItem.item_id) }}</span>
                                <div class="d-flex align-items-center gap-2">
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
                            <i class="ki-duotone ki-printer fs-5">
                                <span class="path1"></span>
                                <span class="path2"></span>
                                <span class="path3"></span>
                                <span class="path4"></span>
                                <span class="path5"></span>
                            </i>
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
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="fs-8 text-gray-600">Paid</span>
                                    <span class="fs-8 text-success">-{{ formatCurrency(selectedOrderDetail.paid_amount) }}</span>
                                </div>
                                <div class="d-flex justify-content-between border-top pt-2 mt-2">
                                    <span class="fs-7 fw-bold">Balance Due</span>
                                    <span class="fs-6 fw-bold text-primary">{{ formatCurrency(selectedOrderDetail.balance_amount) }}</span>
                                </div>
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

        <!-- Order Details Modal (Redesigned) -->
        <div class="modal fade" id="orderDetailModal" tabindex="-1" ref="orderModalRef">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content" v-if="modalOrderDetail">
                    <!-- Modal Header -->
                    <div class="modal-header border-0 pb-0 justify-content-end">
                        <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" @click="closeOrderModal">
                            <i class="ki-duotone ki-cross fs-1">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                        </div>
                    </div>

                    <!-- Modal Body -->
                    <div class="modal-body scroll-y mx-5 mx-xl-15 pt-0 pb-15">
                        <!-- Heading -->
                        <div class="text-center mb-13">
                            <h1 class="mb-3">Order #{{ modalOrderDetail.order_number }}</h1>
                            <div class="d-flex flex-center gap-2">
                                <span class="badge fw-bold px-4 py-3" :class="getStatusBadgeClass(modalOrderDetail.processing_status)">
                                    {{ modalOrderDetail.processing_status }}
                                </span>
                                <span v-if="modalOrderDetail.urgent" class="badge badge-light-danger fw-bold px-4 py-3">URGENT</span>
                            </div>
                        </div>

                        <!-- Info Section -->
                        <div class="row g-9 mb-8">
                            <!-- Customer Info -->
                            <div class="col-md-6">
                                <div class="bg-light rounded p-5 h-100 border border-transparent">
                                    <div class="d-flex align-items-center mb-4">
                                        <div class="symbol symbol-40px symbol-circle me-3">
                                            <span class="symbol-label bg-primary text-inverse-primary fs-4 fw-bold">
                                                {{ modalOrderDetail.customer?.name.charAt(0) }}
                                            </span>
                                        </div>
                                        <div class="d-flex flex-column">
                                            <span class="text-gray-800 fs-5 fw-bold">{{ modalOrderDetail.customer?.name }}</span>
                                            <span class="text-muted fs-7">Customer Details</span>
                                        </div>
                                    </div>
                                    <div class="d-flex flex-column gap-2">
                                        <div class="d-flex align-items-center text-gray-600 fs-6">
                                            <i class="ki-duotone ki-phone fs-4 me-2"><span class="path1"></span><span class="path2"></span></i>
                                            {{ modalOrderDetail.customer?.phone }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Order Meta -->
                            <div class="col-md-6">
                                <div class="bg-light rounded p-5 h-100 border border-transparent">
                                    <div class="d-flex flex-column gap-5">
                                        <div class="d-flex align-items-center">
                                            <div class="symbol symbol-35px me-3">
                                                <span class="symbol-label bg-white">
                                                    <i class="ki-duotone ki-calendar-tick fs-2 text-info"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span><span class="path6"></span></i>
                                                </span>
                                            </div>
                                            <div class="d-flex flex-column">
                                                <span class="text-gray-400 fs-8 fw-bold">CREATED AT</span>
                                                <span class="text-gray-800 fs-7 fw-bold">{{ getTimeAgo(modalOrderDetail.created_at) }}</span>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center">
                                            <div class="symbol symbol-35px me-3">
                                                <span class="symbol-label bg-white">
                                                    <i class="ki-duotone ki-delivery-3 fs-2 text-warning"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                                                </span>
                                            </div>
                                            <div class="d-flex flex-column">
                                                <span class="text-gray-400 fs-8 fw-bold">SERVICE TYPE</span>
                                                <span class="text-gray-800 fs-7 fw-bold">{{ modalOrderDetail.items[0]?.service_name }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Items Section -->
                        <div class="mb-10">
                            <h4 class="text-gray-800 fw-bold mb-5 d-flex align-items-center gap-2">
                                <i class="ki-duotone ki-basket fs-3 text-primary"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i>
                                Order Items
                            </h4>
                            
                            <div class="mh-300px scroll-y px-1">
                                <div v-for="item in modalOrderDetail.items" :key="item.id" 
                                     class="d-flex flex-stack py-4 border-bottom border-gray-300 border-bottom-dashed">
                                    <div class="d-flex align-items-center">
                                        <div class="symbol symbol-35px me-4">
                                            <span class="symbol-label bg-light-primary text-primary fw-bold">
                                                {{ item.quantity }}x
                                            </span>
                                        </div>
                                        <div class="d-flex flex-column">
                                            <span class="text-gray-800 fs-6 fw-bold">{{ item.item_name }}</span>
                                            <span v-if="item.notes" class="text-muted fs-7 italic">{{ item.notes }}</span>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <span class="text-gray-800 fs-6 fw-bold">{{ formatCurrency(item.total_price) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Summary & Pricing -->
                        <div class="bg-gray-100 rounded-2 p-6 mb-10">
                            <div class="d-flex flex-stack mb-2">
                                <span class="text-gray-600 fs-6 fw-bold">Subtotal</span>
                                <span class="text-gray-800 fs-6 fw-bold">{{ formatCurrency(modalOrderDetail.subtotal) }}</span>
                            </div>
                            <div v-if="modalOrderDetail.discount_amount > 0" class="d-flex flex-stack mb-2">
                                <span class="text-gray-600 fs-6 fw-bold">Discount</span>
                                <span class="text-danger fs-6 fw-bold">-{{ formatCurrency(modalOrderDetail.discount_amount) }}</span>
                            </div>
                            <div class="separator separator-dashed my-3"></div>
                            <div class="d-flex flex-stack">
                                <span class="text-gray-800 fs-4 fw-bold">Total Payable</span>
                                <span class="text-primary fs-3 fw-bolder">{{ formatCurrency(modalOrderDetail.total_amount) }}</span>
                            </div>
                        </div>

                         <!-- Actions -->
                        <div class="d-flex flex-stack gap-3">
                            <button
                                type="button"
                                class="btn btn-light-danger fs-7 fw-bold"
                                @click="confirmCancelOrder(modalOrderDetail.id)"
                            >
                                <i class="ki-duotone ki-trash fs-2 me-1"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
                                Cancel Order
                            </button>

                            <div class="d-flex gap-3 flex-grow-1 justify-content-end">
                                <!-- Status IDs: 2=Pending, 3=Washing, 4=Drying, 5=Ready, 6=Delivered -->
                                <button
                                    v-if="modalOrderDetail.processing_status_id === 2"
                                    type="button"
                                    class="btn btn-primary fs-7 fw-bold"
                                    @click="moveAndCloseModal(modalOrderDetail.id, 3)"
                                >
                                    Move to Washing <i class="ki-duotone ki-arrow-right fs-4 ms-1"><span class="path1"></span><span class="path2"></span></i>
                                </button>
                                 <button
                                    v-else-if="modalOrderDetail.processing_status_id === 3"
                                    type="button"
                                    class="btn btn-info fs-7 fw-bold"
                                    @click="moveAndCloseModal(modalOrderDetail.id, 4)"
                                >
                                    Move to Drying <i class="ki-duotone ki-arrow-right fs-4 ms-1"><span class="path1"></span><span class="path2"></span></i>
                                </button>
                                <button
                                    v-else-if="modalOrderDetail.processing_status_id === 4"
                                    type="button"
                                    class="btn btn-success fs-7 fw-bold"
                                    style="background-color: #8b5cf6;"
                                    @click="moveAndCloseModal(modalOrderDetail.id, 5)"
                                >
                                    Mark as Ready <i class="ki-duotone ki-arrow-right fs-4 ms-1"><span class="path1"></span><span class="path2"></span></i>
                                </button>
                                <button
                                    type="button"
                                    class="btn btn-light fs-7 fw-bold"
                                    @click="closeOrderModal"
                                >
                                    Close
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from "vue";
import { usePosStore } from "./PosStore";
import ApiService from "@/core/services/ApiService";
import Swal from "sweetalert2";
import { Modal } from "bootstrap";
import { formatCurrency } from "@utility@/currency";
import { $toastSuccess, $toastError } from "@/core/helpers/utility";

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
});
const searchQuery = ref("");
const searchingCustomer = ref(false);
const customerSuggestions = ref([]);
const customerFound = ref(false);
let customerSearchTimeout = null;

// Modal state
const showOrderModal = ref(false);
const modalOrderDetail = ref(null);
const orderModalRef = ref(null);
let bootstrapModal = null;

const paymentMethods = [
    { value: "cash", label: "Cash" },
    { value: "card", label: "Card" },
    { value: "upi", label: "UPI" },
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
        if (servicePrice && servicePrice.price !== null) {
            return parseFloat(servicePrice.price);
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
        if (servicePrice && servicePrice.price !== null) {
            return parseFloat(servicePrice.price);
        }
    }
    return parseFloat(item.price) || 0;
}

const orderSubtotal = computed(() => {
    return newOrder.value.items.reduce((sum, orderItem) => {
        const price = getItemPrice(orderItem.item_id);
        return sum + price * orderItem.quantity;
    }, 0);
});

const selectedServiceName = computed(() => {
    const service = services.value.find((s) => s.id === newOrder.value.service_id);
    return service?.name || "-";
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
        newOrder.value.items.push({ item_id: item.id, quantity: 1 });
    } else {
        newOrder.value.items.splice(index, 1);
    }
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
        await posStore.createOrder({
            customer_name: newOrder.value.customer_name,
            customer_phone: newOrder.value.customer_phone,
            service_id: newOrder.value.service_id,
            items: newOrder.value.items,
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
        });

        selectedOrderDetail.value = null;
        payment.value = { method: "cash", amount: 0 };

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

// Lifecycle
onMounted(() => {
    posStore.fetchBoardData();

    // Auto-refresh every 30 seconds
    setInterval(() => {
        posStore.refreshStatistics();
    }, 30000);
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
</style>
