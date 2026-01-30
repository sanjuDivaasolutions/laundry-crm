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
 *  *  Last modified: 16/01/25, 10:37 am
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

return [
    'general' => [
        'title' => 'General',
        'fields' => [
            'id' => 'ID',
            'name' => 'Name',
            'title' => 'Title',
            'created_at' => 'Created at',
            'updated_at' => 'Updated at',
            'deleted_at' => 'Deleted at',
            'dashboard' => 'Dashboard',
            'configuration' => 'Configuration',
            'settings' => 'Settings',
            'modules' => 'Modules',
            'save' => 'Save',
            'cancel' => 'Cancel',
            'actions' => 'Actions',
            'inventory' => 'Inventory',
            'signIn' => 'Sign-in',
            'signUp' => 'Sign-up',
            'passwordReset' => 'Password Reset',
            'error404' => 'Error 404',
            'error500' => 'Error 500',
            'noInitialResults' => 'Start searching...',
            'noTableResults' => 'No results found',
            'tableLoading' => 'Please wait...',
            'authLayoutTitle' => 'Sign-in to your account',
            'amount' => 'Amount',
            'subTotal' => 'Sub-Total',
            'discountTotal' => 'Discount Total',
            'taxTotal' => 'Tax Amount',
            'taxRate' => 'Tax Rate',
            'grandTotal' => 'Grand Total',
        ],
    ],
    'permission' => [
        'title' => 'Permissions',
        'title_singular' => 'Permission',
        'fields' => [
            'id' => 'ID',
            'title' => 'Title',
            'permission_group' => 'Group',
        ],
    ],
    'role' => [
        'title' => 'Roles',
        'title_singular' => 'Role',
        'fields' => [
            'id' => 'ID',
            'title' => 'Title',
            'permissions' => 'Permissions',
            'has_admin_access' => 'Has Admin Access',
        ],
    ],
    'user' => [
        'title' => 'Users',
        'title_singular' => 'User',
        'fields' => [
            'id' => 'ID',
            'name' => 'Name',
            'email' => 'Email',
            'email_verified_at' => 'Email verified at',
            'password' => 'Password',
            'roles' => 'Roles',
            'remember_token' => 'Remember Token',
            'active' => 'Active',
            'settings' => 'Settings',
        ],
    ],
    'permissionGroup' => [
        'title' => 'Permission Groups',
        'title_singular' => 'Permission Group',
        'fields' => [
            'id' => 'ID',
            'name' => 'Name',
        ],
    ],
    'localization' => [
        'title' => 'Localization',
        'title_singular' => 'Localization',
    ],
    'language' => [
        'title' => 'Languages',
        'title_singular' => 'Language',
        'fields' => [
            'id' => 'ID',
            'name' => 'Name',
            'active' => 'Active',
            'locale' => 'Locale',
            'translations' => 'Translations',
        ],
    ],
    'languageTerm' => [
        'title' => 'Language Terms',
        'title_singular' => 'Language Term',
        'fields' => [
            'id' => 'ID',
            'name' => 'Name',
            'active' => 'Active',
            'language_term_group' => 'Language Term Group',
        ],
    ],
    'translation' => [
        'title' => 'Translations',
        'title_singular' => 'Translation',
        'fields' => [
            'id' => 'ID',
            'language' => 'Language',
            'language_term' => 'Language Term',
            'translation' => 'Translation',
        ],
    ],
    'languageTermGroup' => [
        'title' => 'Language Term Group',
        'title_singular' => 'Language Term Group',
        'fields' => [
            'id' => 'ID',
            'name' => 'Name',
        ],
    ],
    'country' => [
        'title' => 'Countries',
        'title_singular' => 'Country',
        'fields' => [
            'id' => 'ID',
            'name' => 'Name',
            'active' => 'Active',
        ],
    ],
    'state' => [
        'title' => 'States',
        'title_singular' => 'State',
        'fields' => [
            'id' => 'ID',
            'name' => 'Name',
            'active' => 'Active',
            'country' => 'Country',
        ],
    ],
    'city' => [
        'title' => 'Cities',
        'title_singular' => 'City',
        'fields' => [
            'id' => 'ID',
            'name' => 'Name',
            'state' => 'State',
            'active' => 'Active',
        ],
    ],
    'master' => [
        'title' => 'Masters',
        'title_singular' => 'Master',
    ],
    'media' => [
        'title' => 'Media',
        'title_singular' => 'Media',
    ],
    'customer' => [
        'title' => 'Customers',
        'title_singular' => 'Customer',
        'fields' => [
            'id' => 'ID',
            'name' => 'Name',
            'phone' => 'Phone',
            'address' => 'Address',
            'customer_code' => 'Code',
        ],
    ],
    'order' => [
        'title' => 'Orders',
        'title_singular' => 'Order',
        'fields' => [
            'id' => 'ID',
            'order_number' => 'Order #',
            'order_date' => 'Order Date',
            'promised_date' => 'Promised Date',
            'total_amount' => 'Total',
            'payment_status' => 'Payment Status',
            'order_status' => 'Order Status',
        ],
    ],
];
