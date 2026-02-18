<?php

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
 *  *  Last modified: 05/02/25, 5:27 pm
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

return [
    'general' => [
        'fields' => [
            'document' => 'Document',
            'order_quantity' => 'Order Quantity',
            'received_quantity' => 'Received Quantity',
            'balance_quantity' => 'Balance Quantity',
            'packed_quantity' => 'Packed Quantity',
            'estimated_delivery_date' => 'Estimated Delivery Date',
            'is_taxable' => 'Taxable?',
            'target_shelf' => 'Target Shelf',
            'shelf_stock' => 'Shelf Stock',
            'total_stock' => 'Total Stock',
            'grand_total' => 'Grand Total',
            'month' => 'Month',
            'total_sales' => 'Total Sales',
            'phone' => 'Phone',
            'agent' => 'Agent',
            'total_commission' => 'Total Commission',
        ],
        'events' => 'Events',
        'remarks' => 'Remarks',
        'updated' => 'Updated',
    ],
    'order' => [
        'title' => 'Orders',
        'title_singular' => 'Order',
        'fields' => [
            'order_number' => 'Order Number',
            'order_date' => 'Order Date',
            'promised_date' => 'Promised Date',
            'total_amount' => 'Total Amount',
            'payment_status' => 'Payment Status',
            'order_status' => 'Order Status',
            'processing_status' => 'Processing Status',
            'urgent' => 'Urgent',
            'hanger_number' => 'Hanger Number',
            'notes' => 'Notes',
            'discount_type' => 'Discount Type',
            'discount_amount' => 'Discount Amount',
            'tax_rate' => 'Tax Rate',
            'tax_amount' => 'Tax Amount',
            'paid_amount' => 'Paid Amount',
            'balance_amount' => 'Balance Amount',
            'picked_up_at' => 'Picked Up At',
            'history_timeline_title' => 'Status Timeline',
            'history_timeline_subtitle' => 'Complete order lifecycle tracking',
            'history_empty_title' => 'No Status History Yet',
            'history_empty_description' => 'Status changes will appear here as the order progresses',
        ],
        'notifications' => [
            'status_changed' => 'Order :order_number status changed to :status',
            'ready_for_pickup' => 'Your laundry order :order_number is ready for pickup!',
            'payment_received' => 'Payment of :amount received for order :order_number',
            'order_created' => 'New order :order_number has been created',
            'order_overdue' => 'Order :order_number is overdue for pickup',
        ],
    ],
    'customer' => [
        'title' => 'Customers',
        'title_singular' => 'Customer',
        'fields' => [
            'name' => 'Name',
            'customer_code' => 'Customer Code',
            'phone' => 'Phone',
            'address' => 'Address',
            'city' => 'City',
            'pincode' => 'Pincode',
            'email' => 'Email',
            'tax_number' => 'Tax Number',
            'credit_limit' => 'Credit Limit',
            'current_balance' => 'Current Balance',
            'is_active' => 'Active',
        ],
    ],
    'delivery' => [
        'title' => 'Deliveries',
        'title_singular' => 'Delivery',
        'fields' => [
            'scheduled_date' => 'Scheduled Date',
            'scheduled_time' => 'Scheduled Time',
            'delivery_type' => 'Type',
            'pickup' => 'Pickup',
            'drop_off' => 'Drop Off',
            'driver' => 'Driver',
            'status' => 'Status',
            'address' => 'Delivery Address',
            'notes' => 'Notes',
        ],
    ],
    'report' => [
        'title' => 'Reports',
        'title_singular' => 'Report',
        'fields' => [
            'date_range' => 'Date Range',
            'total_revenue' => 'Total Revenue',
            'total_orders' => 'Total Orders',
            'average_order_value' => 'Average Order Value',
            'pending_orders' => 'Pending Orders',
            'completed_orders' => 'Completed Orders',
            'top_services' => 'Top Services',
            'top_customers' => 'Top Customers',
            'revenue_trend' => 'Revenue Trend',
            'daily_summary' => 'Daily Summary',
            'weekly_summary' => 'Weekly Summary',
            'monthly_summary' => 'Monthly Summary',
        ],
    ],
    'company' => [
        'title' => 'Companies',
        'title_singular' => 'Company',
        'fields' => [
            'company' => 'Company',
            'image' => 'Image',
            'code' => 'Code',
            'name' => 'Name',
            'address_1' => 'Address 1',
            'address_2' => 'Address 2',
            'country' => 'Country',
            'state' => 'State',
            'city' => 'City',
            'postal_code' => 'Postal Code',
        ],
    ],
    'mnuContract' => [
        'title' => 'Contracts',
        'title_singular' => 'Contract',
    ],
    'contract' => [
        'title' => 'Contracts',
        'title_singular' => 'Contract',
        'fields' => [
            'id' => 'ID',
            'client' => 'Client',
            'date' => 'Date',
            'start_date' => 'Start Date',
            'end_date' => 'End Date',
            'code' => 'Code',
            'installment_count' => 'Number of Installments',
            'installment_remaining' => 'Remaining Installments',
            'amount_remaining' => 'Remaining Amount',
        ],
    ],
    'installment' => [
        'title' => 'Installments',
        'title_singular' => 'Installment',
        'fields' => [
            'id' => 'ID',
            'contract' => 'Contract',
            'date' => 'Date',
            'amount' => 'Amount',
            'code' => 'Code',
            'serial_number' => 'Serial Number',
        ],
    ],
    'contractItem' => [
        'title' => 'Contract Item',
        'title_singular' => 'Contract Item',
        'fields' => [
            'id' => 'ID',
            'contract' => 'Contract',
            'service' => 'Service',
            'description' => 'Description',
            'remark' => 'Remark',
            'amount' => 'Amount',
        ],
    ],
    'contractTerm' => [
        'title' => 'Contract Terms',
        'title_singular' => 'Contract Term',
        'fields' => [
            'id' => 'ID',
            'name' => 'Name',
            'description' => 'Description',
            'sequence' => 'Sequence',
            'active' => 'Active',
        ],
    ],
    'contractRevision' => [
        'title' => 'Contract Revision',
        'title_singular' => 'Contract Revision',
        'fields' => [
            'id' => 'ID',
            'start_date' => 'Start Date',
            'end_date' => 'End Date',
            'installment_count' => 'Installment Count',
            'limited_installment' => 'Limited Installments',
            'contract' => 'Contract',
            'active' => 'Active',
            'user' => 'User',
            'code' => 'Code',
            'other_terms' => 'Other Terms',
            'contract_remark' => 'Remark (For Internal Use)',
            'remark' => 'Remark',
            'contract_term' => 'Contract Term',
            'company_name' => 'Company Name',
            'contract_type' => 'Contract Type',
        ],
    ],
    'service' => [
        'title' => 'Services',
        'title_singular' => 'Service',
    ],
    'serviceInvoice' => [
        'title' => 'Service Invoices',
        'title_singular' => 'Service Invoice',
    ],
    'inward' => [
        'title' => 'Inwards',
        'title_singular' => 'Inward',
    ],
    'paymentMethod' => [
        'title' => 'Payment Methods',
        'title_singular' => 'Payment Method',
    ],
    'expenseType' => [
        'title' => 'Expense Types',
        'title_singular' => 'Expense Type',
    ],
    'expense' => [
        'title' => 'Expenses',
        'title_singular' => 'Expense',
        'fields' => [
            'expense_type' => 'Expense Type',
            'payment_method' => 'Payment Method',
        ],
    ],
    'quotation' => [
        'title' => 'Quotations',
        'title_singular' => 'Quotation',
        'fields' => [
            'expected_delivery_date' => 'Expected Delivery Date',
            'order_no' => 'Order #',
        ],
    ],
    'package' => [
        'title' => 'Packages',
        'title_singular' => 'Package',
        'fields' => [
            'code' => 'Code',
            'reference_no' => 'Reference #',
            'total_boxes' => 'Total Boxes',
            'date' => 'Date',
            'buyer_id' => 'Buyer',
            'sales_invoice' => 'Sales Invoice',
            'user_id' => 'User',
        ],
    ],
    'inventoryAdjustment' => [
        'title' => 'Inventory Adjustments',
        'title_singular' => 'Inventory Adjustment',
        'fields' => [
            'adjusted_quantity' => 'Adjusted Quantity',
            'adjusted_quantity_form' => 'Adjust/Move Quantity',
        ],
    ],
    'mnuReport' => [
        'title' => 'Reports',
        'title_singular' => 'Report',
    ],
    'report' => [
        'title' => 'Reports',
        'title_singular' => 'Report',
    ],
    'mnuNewsletter' => [
        'title' => 'Newsletters',
        'title_singular' => 'Newsletter',
    ],
    'message' => [
        'title' => 'Messages',
        'title_singular' => 'Message',
        'fields' => [
            'schedule_at' => 'Schedule',
            'status' => 'Status',
            'message' => 'Message',
            'subject' => 'Subject',
        ],
    ],
    'subscriber' => [
        'title' => 'Subscribers',
        'title_singular' => 'Subscriber',
    ],
];
