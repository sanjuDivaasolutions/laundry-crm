# Quotation to Sales Order Conversion

This document describes the implementation of the quotation to sales order conversion functionality in the Mega Sign Rental ERP system.

## Overview

The quotation to sales conversion feature allows users to convert approved quotations into sales orders with the following capabilities:

- Convert all items or selected items from a quotation
- Modify quantities and prices during conversion
- Set warehouse and delivery information
- Add customer notes and terms
- Automatic sales order number generation
- Quotation status update to "converted"

## Backend Implementation

### API Endpoints

#### Convert Quotation to Sales Order
```
POST /api/quotations/{quotation}/convert-to-sales-order
```

**Request Body:**
```json
{
  "warehouse_id": "required|exists:warehouses,id",
  "customer_notes": "nullable|string|max:1000",
  "terms_and_conditions": "nullable|string|max:2000",
  "expected_delivery_date": "nullable|date|after:today",
  "payment_terms": "nullable|string|max:255",
  "sales_person_id": "nullable|exists:users,id",
  "convert_all_items": "boolean",
  "selected_items": "required_if:convert_all_items,false|array",
  "selected_items.*.id": "required|exists:quotation_items,id",
  "selected_items.*.quantity": "required|numeric|min:1",
  "selected_items.*.unit_price": "required|numeric|min:0"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Quotation converted to sales order successfully",
  "data": {
    "id": 1,
    "sales_order_number": "SO2024110001",
    "buyer_id": 1,
    "quotation_id": 1,
    "status": "pending",
    // ... other sales order fields
  }
}
```

#### Get Conversion Preview
```
GET /api/quotations/{quotation}/conversion-preview
```

**Response:**
```json
{
  "success": true,
  "data": {
    "quotation_number": "QT2024110001",
    "customer": {
      "id": 1,
      "name": "Customer Name",
      "email": "customer@example.com",
      "phone": "+1234567890"
    },
    "items": [
      {
        "id": 1,
        "product_id": 1,
        "product_name": "Product Name",
        "product_sku": "SKU001",
        "description": "Product description",
        "quantity": 5,
        "unit_price": 100.00,
        "discount_percentage": 10.00,
        "tax_rate": 15.00,
        "total": 525.00
      }
    ],
    "subtotal": 500.00,
    "tax_amount": 75.00,
    "discount_amount": 50.00,
    "total_amount": 525.00
  }
}
```

### Service Layer

#### QuotationConversionService

The `QuotationConversionService` handles the business logic for converting quotations to sales orders:

**Key Methods:**
- `convertToSalesOrder(Quotation $quotation, array $data): SalesOrder`
- `previewSalesOrder(Quotation $quotation): array`

**Features:**
- Database transaction handling
- Automatic sales order number generation
- Item total calculation with discounts and taxes
- Quotation status updates
- Multi-tenancy support

### Models

#### Quotation Model Updates
Add the following relationship to the Quotation model:

```php
public function salesOrders()
{
    return $this->hasMany(SalesOrder::class);
}
```

#### SalesOrder Model Updates
Add the following relationship to the SalesOrder model:

```php
public function quotation()
{
    return $this->belongsTo(Quotation::class);
}
```

## Frontend Implementation

### Vue Components

#### Show.vue
Updated quotation show page with conversion button and modal integration.

#### ConversionModal.vue
Modal component for handling the conversion process with:
- Warehouse selection
- Item selection (all or specific)
- Quantity and price modification
- Delivery date setting
- Notes and terms configuration

#### ConversionStore.js
Pinia store for managing conversion state and API calls.

### User Interface Features

1. **Convert Button**: Prominent button on quotation show page
2. **Conversion Modal**: Comprehensive form with validation
3. **Item Selection**: Checkbox selection with quantity/price editing
4. **Real-time Validation**: Form validation with error messages
5. **Loading States**: Spinner during conversion process
6. **Success Handling**: Automatic redirect to created sales order

## Database Schema

### Sales Orders Table
Ensure the sales_orders table includes:
- `quotation_id` (nullable, foreign key)
- `expected_delivery_date` (nullable, date)
- `warehouse_id` (required, foreign key)

### Quotations Table
Ensure the quotations table includes:
- `status` (enum: pending, approved, rejected, converted, cancelled)

## Testing

### Feature Tests
- Convert quotation with all items
- Convert quotation with selected items
- Generate unique sales order numbers
- Preview conversion data
- Handle validation errors

### Test Coverage
- Service layer business logic
- API endpoint responses
- Form validation
- Database transactions
- Multi-tenancy scenarios

## Security Considerations

1. **Authorization**: Users need `convert-quotations` permission
2. **Validation**: Comprehensive input validation
3. **Transaction Safety**: Database transactions for data integrity
4. **Scope Isolation**: Multi-tenant data isolation

## Usage Flow

1. User navigates to an approved quotation
2. Clicks "Convert to Sales Order" button
3. Modal opens with quotation summary
4. User selects warehouse and conversion options
5. User can modify quantities and prices for selected items
6. User sets delivery date and additional notes
7. System validates and processes conversion
8. Quotation status updates to "converted"
9. User is redirected to the new sales order

## Error Handling

- Validation errors displayed in form
- API errors shown as toast notifications
- Network errors handled gracefully
- Database transaction rollback on failures

## Performance Considerations

- Efficient database queries with relationships
- Lazy loading of warehouse and user data
- Minimal API calls for conversion preview
- Optimized item total calculations