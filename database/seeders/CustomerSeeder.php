<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $tenantId = 1; // Default tenant

        $customers = [
            ['tenant_id' => $tenantId, 'customer_code' => 'CUST-1001', 'name' => 'John Smith', 'phone' => '+1-555-0101', 'address' => '123 Main St, New York, NY 10001', 'is_active' => true],
            ['tenant_id' => $tenantId, 'customer_code' => 'CUST-1002', 'name' => 'Sarah Johnson', 'phone' => '+1-555-0102', 'address' => '456 Oak Ave, Brooklyn, NY 11201', 'is_active' => true],
            ['tenant_id' => $tenantId, 'customer_code' => 'CUST-1003', 'name' => 'Michael Brown', 'phone' => '+1-555-0103', 'address' => '789 Pine Rd, Queens, NY 11354', 'is_active' => true],
            ['tenant_id' => $tenantId, 'customer_code' => 'CUST-1004', 'name' => 'Emily Davis', 'phone' => '+1-555-0104', 'address' => '321 Elm St, Manhattan, NY 10002', 'is_active' => true],
            ['tenant_id' => $tenantId, 'customer_code' => 'CUST-1005', 'name' => 'David Wilson', 'phone' => '+1-555-0105', 'address' => '654 Maple Dr, Bronx, NY 10451', 'is_active' => true],
            ['tenant_id' => $tenantId, 'customer_code' => 'CUST-1006', 'name' => 'Jennifer Martinez', 'phone' => '+1-555-0106', 'address' => '987 Cedar Ln, Staten Island, NY 10301', 'is_active' => true],
            ['tenant_id' => $tenantId, 'customer_code' => 'CUST-1007', 'name' => 'Robert Taylor', 'phone' => '+1-555-0107', 'address' => '147 Birch Blvd, New York, NY 10003', 'is_active' => true],
            ['tenant_id' => $tenantId, 'customer_code' => 'CUST-1008', 'name' => 'Lisa Anderson', 'phone' => '+1-555-0108', 'address' => '258 Spruce St, Brooklyn, NY 11202', 'is_active' => true],
            ['tenant_id' => $tenantId, 'customer_code' => 'CUST-1009', 'name' => 'James Thomas', 'phone' => '+1-555-0109', 'address' => '369 Willow Way, Queens, NY 11355', 'is_active' => true],
            ['tenant_id' => $tenantId, 'customer_code' => 'CUST-1010', 'name' => 'Mary Jackson', 'phone' => '+1-555-0110', 'address' => '741 Ash Ave, Manhattan, NY 10004', 'is_active' => true],
        ];

        foreach ($customers as $customerData) {
            Customer::create($customerData);
        }
    }
}
