# Manufacturing Orders API - Usage Examples

## Create a Manufacturing Order

### Request
```bash
POST /api/manufacturing-orders
Content-Type: application/json

{
  "product_id": 1,
  "product_name": "Balata 113x113",
  "quantity_produced": 50,
  "cost_per_unit": 150.00,
  "total_cost": 7500.00,
  "selling_price_per_unit": 180.00,
  "notes": "Production batch for customer order",
  "components": [
    {
      "component_name": "Wood Panel 18mm",
      "quantity": 100,
      "unit": "m2",
      "unit_cost": 45.00
    },
    {
      "component_name": "Nails",
      "quantity": 5,
      "unit": "kg",
      "unit_cost": 12.00
    },
    {
      "component_name": "Labor",
      "quantity": 8,
      "unit": "hours",
      "unit_cost": 25.00
    }
  ]
}
```

### Response
```json
{
  "success": true,
  "message": "Manufacturing order created successfully",
  "data": {
    "id": 1,
    "order_number": "MO-2026-0001",
    "product_id": 1,
    "product_name": "Balata 113x113",
    "quantity_produced": "50.00",
    "cost_per_unit": "150.0000",
    "total_cost": "7500.0000",
    "selling_price_per_unit": "180.0000",
    "status": "draft",
    "notes": "Production batch for customer order",
    "created_at": "2026-04-24T10:30:00.000000Z",
    "components": [
      {
        "id": 1,
        "component_name": "Wood Panel 18mm",
        "quantity": "100.0000",
        "unit": "m2",
        "unit_cost": "45.0000",
        "total_cost": "4500.0000"
      },
      {
        "id": 2,
        "component_name": "Nails",
        "quantity": "5.0000",
        "unit": "kg",
        "unit_cost": "12.0000",
        "total_cost": "60.0000"
      },
      {
        "id": 3,
        "component_name": "Labor",
        "quantity": "8.0000",
        "unit": "hours",
        "unit_cost": "25.0000",
        "total_cost": "200.0000"
      }
    ]
  }
}
```

## List Manufacturing Orders

### Request
```bash
GET /api/manufacturing-orders?status=draft&page=1&per_page=20
```

### Response
```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "order_number": "MO-2026-0001",
        "product_name": "Balata 113x113",
        "quantity_produced": "50.00",
        "total_cost": "7500.0000",
        "status": "draft",
        "created_at": "2026-04-24T10:30:00.000000Z"
      }
    ],
    "total": 1,
    "per_page": 20
  }
}
```

## Confirm a Manufacturing Order

### Request
```bash
POST /api/manufacturing-orders/1/confirm
```

### Response
```json
{
  "success": true,
  "message": "Manufacturing order confirmed successfully",
  "data": {
    "id": 1,
    "order_number": "MO-2026-0001",
    "status": "confirmed",
    "product_name": "Balata 113x113",
    "quantity_produced": "50.00",
    "total_cost": "7500.0000"
  }
}
```

## Complete Order & Add to Inventory

### Request
```bash
POST /api/manufacturing-orders/1/complete
Content-Type: application/json

{
  "warehouse_id": 1,
  "product_id": 1
}
```

### Response
```json
{
  "success": true,
  "message": "Manufacturing order completed and inventory updated successfully",
  "data": {
    "id": 1,
    "order_number": "MO-2026-0001",
    "status": "completed",
    "product_id": 1,
    "quantity_produced": "50.00",
    "cost_per_unit": "150.0000",
    "selling_price_per_unit": "180.0000",
    "produced_at": "2026-04-24T11:00:00.000000Z",
    "completed_by": 1,
    "inventory_movements": [
      {
        "id": 123,
        "movement_number": "PRD-20260424-110000-123",
        "movement_type": "production",
        "quantity_change": "50.00",
        "quantity_after": "50.00",
        "unit_cost": "150.00",
        "unit_price": "180.00"
      }
    ]
  }
}
```

## Get Manufacturing Order Statistics

### Request
```bash
GET /api/manufacturing-orders/statistics?date_from=2026-01-01&date_to=2026-12-31
```

### Response
```json
{
  "success": true,
  "data": {
    "total_orders": 45,
    "draft_orders": 5,
    "confirmed_orders": 3,
    "completed_orders": 35,
    "cancelled_orders": 2,
    "total_manufacturing_cost": "125000.00",
    "total_quantity_produced": "1500.00"
  }
}
```

## Calculate Costs from Components

### Request
```bash
POST /api/manufacturing-orders/calculate
Content-Type: application/json

{
  "components": [
    {
      "component_name": "Wood Panel 18mm",
      "quantity": 100,
      "unit_cost": 45.00
    },
    {
      "component_name": "Nails",
      "quantity": 5,
      "unit_cost": 12.00
    }
  ]
}
```

### Response
```json
{
  "success": true,
  "data": {
    "components_total_cost": "4560.0000"
  }
}
```

## Error Scenarios

### Validation Error
```json
{
  "success": false,
  "message": "The given data was invalid.",
  "errors": {
    "quantity_produced": ["Quantity produced must be greater than 0"],
    "selling_price_per_unit": ["Selling price should be greater than or equal to cost price"]
  }
}
```

### Cannot Complete Confirmed Order
```json
{
  "success": false,
  "message": "Failed to complete order: Cannot complete an order that is draft"
}
```

### Product Not Found
```json
{
  "success": false,
  "message": "Failed to complete order: No query results for model [App\\Models\\Product] 999"
}
```

## Complete Workflow Example

### 1. Create Draft Order
```bash
POST /api/manufacturing-orders
{
  "product_name": "Premium Pallet 120x80",
  "quantity_produced": 25,
  "cost_per_unit": 120.00,
  "total_cost": 3000.00,
  "selling_price_per_unit": 150.00,
  "components": [...]
}
```

### 2. Confirm for Production
```bash
POST /api/manufacturing-orders/1/confirm
```

### 3. Complete & Add to Inventory
```bash
POST /api/manufacturing-orders/1/complete
{
  "warehouse_id": 1,
  "product_id": 5
}
```

### 4. Verify Inventory Update
```bash
GET /api/inventory-movements?product_id=5&movement_type=production
```

## Status Transitions

```
draft → confirmed → completed
  ↓         ↓           ↓
cancelled  cancelled   (read-only)
```

- **draft**: Can edit, confirm, or cancel
- **confirmed**: Can complete or cancel (read-only otherwise)
- **completed**: Read-only (archived)
- **cancelled**: Read-only (archived)