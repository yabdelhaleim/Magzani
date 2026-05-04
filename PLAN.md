# Magzani ERP - Filament Migration Plan

> **Goal:** Migrate the entire Magzani ERP system from Laravel Blade + custom controllers to Laravel Filament PHP admin panel, preserving every single piece of business logic, validation rule, authorization gate, event/listener flow, and database operation exactly as-is.

> **Architecture:** Replace all Blade views + controllers with Filament Resources, Pages, Actions, and Widgets. Keep the existing Service layer, Event/Listener system, Models, Migrations, Jobs, Observers, and Policies untouched. Filament Resources act as the new "controllers + views" layer.

> **Tech Stack:** Laravel 10, Filament 3.x, PHP 8.1+, MySQL, existing composer packages (dompdf, maatwebsite/excel, spatie/backup)

---

## Table of Contents

1. [Project Overview](#1-project-overview)
2. [Database Schema - Complete](#2-database-schema---complete)
3. [Models - Complete](#3-models---complete)
4. [Authorization System](#4-authorization-system)
5. [Authentication Flow](#5-authentication-flow)
6. [Event/Listener System](#6-eventlistener-system)
7. [Service Layer - Complete Business Logic](#7-service-layer---complete-business-logic)
8. [Form Request Validation Rules](#8-form-request-validation-rules)
9. [Filament Resources](#9-filament-resources)
10. [Filament Pages & Widgets](#10-filament-pages--widgets)
11. [Exports](#11-exports)
12. [Jobs & Observers](#12-jobs--observers)
13. [Notifications](#13-notifications)
14. [Console Commands](#14-console-commands)
15. [Configuration](#15-configuration)
16. [Implementation Order](#16-implementation-order)

---

## 1. Project Overview

### 1.1 What Magzani Does

Magzani is an Arabic-language ERP system (RTL) that manages:
- **Products** with multi-unit pricing (base units, selling units, unit conversions)
- **Warehouses** with stock tracking per product per warehouse
- **Sales Invoices** with items, payments, status tracking (draft/pending/paid/cancelled)
- **Purchase Invoices** with items, payments, status tracking
- **Sales Returns** and **Purchase Returns**
- **Warehouse Transfers** between warehouses with stock movement tracking
- **Warehouse Inbound/Outbound Orders**
- **Stock Counts** with full lifecycle (draft -> counting -> completed -> approved)
- **Manufacturing** - cost calculators and manufacturing orders with BOM components
- **Accounting** - treasury, cash transactions (deposits/withdrawals), expenses, payments
- **Customers** and **Suppliers** with statements and balance tracking
- **Reports** - financial, inventory, profit/loss
- **User Management** with dual RBAC (simple role column + pivot-table permissions)
- **System Settings** - company info, system preferences

### 1.2 File Counts

| Category | Count |
|----------|-------|
| Controllers | 28 |
| Models | 46 |
| Services | 26 |
| Form Requests | 31 |
| Middleware | 2 custom |
| Events | 16 |
| Listeners | 20 |
| Notifications | 10 |
| Exports | 9 |
| Policies | 2 |
| Traits | 5 |
| Jobs | 1 |
| Observers | 1 |
| Migrations | 62 |
| Seeders | 11 |
| Blade Views | 86 |

### 1.3 Key Packages

```json
{
  "barryvdh/laravel-dompdf": "^3.1",
  "doctrine/dbal": "^3.10",
  "maatwebsite/excel": "^3.1",
  "spatie/laravel-backup": "^8.8",
  "laravel/sanctum": "^3.3",
  "predis/predis": "^3.3"
}
```

**To add:**
```
filament/filament: "^3.2"
```

---

## 2. Database Schema - Complete

### 2.1 users

| Column | Type | Nullable | Default | Notes |
|--------|------|----------|---------|-------|
| id | bigint PK AI | NO | | |
| name | varchar(255) | NO | | |
| email | varchar(255) | NO | | UNIQUE |
| password | varchar(255) | NO | | |
| phone | varchar(20) | YES | NULL | |
| is_active | boolean | NO | true | |
| role | enum('admin','employee') | NO | 'employee' | |
| remember_token | varchar(100) | YES | NULL | |
| created_at | timestamp | YES | NULL | |
| updated_at | timestamp | YES | NULL | |
| deleted_at | timestamp | YES | NULL | softDeletes |

**Indexes:** email (unique), is_active, role

### 2.2 roles

| Column | Type | Nullable | Default |
|--------|------|----------|---------|
| id | bigint PK AI | NO | |
| name | varchar(100) | NO | UNIQUE |
| display_name | varchar(255) | YES | NULL |
| description | text | YES | NULL |
| color | varchar(7) | YES | NULL |
| is_system | boolean | NO | false |
| created_at/updated_at/deleted_at | timestamps | YES | NULL |

### 2.3 permissions

| Column | Type | Nullable | Default |
|--------|------|----------|---------|
| id | bigint PK AI | NO | |
| name | varchar(100) | NO | UNIQUE |
| display_name | varchar(255) | YES | NULL |
| description | text | YES | NULL |
| module | varchar(50) | YES | NULL |
| action | varchar(50) | YES | NULL |
| is_system | boolean | NO | false |
| timestamps | | YES | NULL |

### 2.4 role_user (pivot)

| Column | Type |
|--------|------|
| role_id | bigint FK |
| user_id | bigint FK |
| created_at | timestamp |

### 2.5 permission_role (pivot)

| Column | Type |
|--------|------|
| permission_id | bigint FK |
| role_id | bigint FK |

### 2.6 permission_user (pivot)

| Column | Type |
|--------|------|
| permission_id | bigint FK |
| user_id | bigint FK |

### 2.7 warehouses

| Column | Type | Nullable | Default | Notes |
|--------|------|----------|---------|-------|
| id | bigint PK AI | NO | | |
| name | varchar(255) | NO | | |
| code | varchar(50) | NO | | UNIQUE |
| location | text | YES | NULL | |
| is_active | boolean | NO | true | |
| created_by | bigint FK users | YES | NULL | |
| updated_by | bigint FK users | YES | NULL | |
| timestamps + softDeletes | | YES | | |

### 2.8 categories

| Column | Type | Nullable | Default |
|--------|------|----------|---------|
| id | bigint PK AI | NO | |
| name | varchar(255) | NO | |
| description | text | YES | NULL |
| is_active | boolean | NO | true |
| timestamps | | YES | NULL |

### 2.9 products

| Column | Type | Nullable | Default | Notes |
|--------|------|----------|---------|-------|
| id | bigint PK AI | NO | | |
| name | varchar(255) | NO | | |
| code | varchar(100) | YES | NULL | |
| sku | varchar(100) | YES | NULL | UNIQUE |
| barcode | varchar(100) | YES | NULL | |
| description | text | YES | NULL | |
| category_id | bigint FK categories | YES | NULL | |
| purchase_price | decimal(15,2) | NO | 0.00 | |
| selling_price | decimal(15,2) | NO | 0.00 | |
| min_stock | integer | NO | 10 | |
| unit_type | varchar(50) | YES | 'piece' | |
| image | varchar(255) | YES | NULL | |
| is_active | boolean | NO | true | |
| product_type | enum('raw','finished','semi_finished') | NO | 'finished' | |
| is_manufactured | boolean | NO | false | |
| timestamps + softDeletes | | YES | | |

### 2.10 product_warehouse (pivot)

| Column | Type | Nullable | Default |
|--------|------|----------|---------|
| product_id | bigint FK | NO | |
| warehouse_id | bigint FK | NO | |
| quantity | decimal(15,2) | NO | 0 |
| min_stock | integer | NO | 10 |
| reserved_quantity | decimal(15,2) | NO | 0 |
| created_at/updated_at | timestamps | YES | NULL |

**Composite PK:** (product_id, warehouse_id)

### 2.11 product_base_units

| Column | Type | Nullable | Default | Notes |
|--------|------|----------|---------|-------|
| id | bigint PK AI | NO | | |
| product_id | bigint FK products | NO | | |
| unit_code | varchar(50) | NO | | e.g. 'kg', 'piece' |
| unit_name | varchar(100) | NO | | |
| base_purchase_price | decimal(15,4) | NO | 0 | |
| base_selling_price | decimal(15,4) | NO | 0 | |
| auto_update_selling_units | boolean | NO | false | |
| timestamps + softDeletes | | YES | | |

### 2.12 product_selling_units

| Column | Type | Nullable | Default |
|--------|------|----------|---------|
| id | bigint PK AI | NO | |
| product_id | bigint FK products | NO | |
| base_unit_id | bigint FK product_base_units | NO | |
| unit_code | varchar(50) | NO | |
| unit_name | varchar(100) | NO | |
| conversion_factor | decimal(15,4) | NO | 1 |
| is_base | boolean | NO | false |
| auto_calculate_price | boolean | NO | true |
| unit_purchase_price | decimal(15,4) | NO | 0 |
| unit_selling_price | decimal(15,4) | NO | 0 |
| is_active | boolean | NO | true |
| timestamps | | YES | NULL |

### 2.13 product_base_pricing

| Column | Type | Nullable | Default |
|--------|------|----------|---------|
| id | bigint PK AI | NO | |
| product_id | bigint FK products | NO | |
| base_unit | varchar(50) | NO | |
| base_purchase_price | decimal(15,4) | NO | 0 |
| base_selling_price | decimal(15,4) | NO | 0 |
| profit_margin | decimal(5,2) | NO | 0 |
| effective_from | date | YES | NULL |
| is_current | boolean | NO | true |
| created_by | bigint | YES | NULL |
| updated_by | bigint | YES | NULL |
| timestamps | | YES | NULL |

### 2.14 product_price_history

| Column | Type | Nullable | Default |
|--------|------|----------|---------|
| id | bigint PK AI | NO | |
| product_id | bigint FK products | NO | |
| unit_code | varchar(50) | NO | |
| old_price | decimal(15,2) | YES | NULL |
| new_price | decimal(15,2) | YES | NULL |
| change_type | varchar(50) | NO | |
| changed_by | bigint | YES | NULL |
| reason | text | YES | NULL |
| timestamps | | YES | NULL |

### 2.15 price_change_history

| Column | Type | Nullable | Default |
|--------|------|----------|---------|
| id | bigint PK AI | NO | |
| product_id | bigint FK | NO | |
| old_price | decimal(15,4) | YES | NULL |
| new_price | decimal(15,4) | YES | NULL |
| old_purchase_price | decimal(15,4) | YES | NULL |
| new_purchase_price | decimal(15,4) | YES | NULL |
| change_reason | varchar(255) | YES | NULL |
| changed_by | bigint | YES | NULL |
| timestamps | | YES | NULL |

### 2.16 customers

| Column | Type | Nullable | Default |
|--------|------|----------|---------|
| id | bigint PK AI | NO | |
| name | varchar(255) | NO | |
| code | varchar(50) | YES | UNIQUE |
| phone | varchar(20) | YES | NULL |
| email | varchar(255) | YES | NULL |
| address | text | YES | NULL |
| balance | decimal(15,2) | NO | 0.00 |
| credit_limit | decimal(15,2) | YES | NULL |
| is_active | boolean | NO | true |
| type | enum('individual','company') | NO | 'individual' |
| notes | text | YES | NULL |
| timestamps + softDeletes | | YES | NULL |

### 2.17 suppliers

| Column | Type | Nullable | Default |
|--------|------|----------|---------|
| id | bigint PK AI | NO | |
| name | varchar(255) | NO | |
| code | varchar(50) | YES | UNIQUE |
| phone | varchar(20) | YES | NULL |
| email | varchar(255) | YES | NULL |
| address | text | YES | NULL |
| balance | decimal(15,2) | NO | 0.00 |
| is_active | boolean | NO | true |
| notes | text | YES | NULL |
| timestamps + softDeletes | | YES | NULL |

### 2.18 sales_invoices

| Column | Type | Nullable | Default | Notes |
|--------|------|----------|---------|-------|
| id | bigint PK AI | NO | | |
| invoice_number | varchar(100) | NO | | UNIQUE |
| invoice_date | date | NO | | |
| due_date | date | YES | NULL | |
| customer_id | bigint FK customers | NO | | |
| warehouse_id | bigint FK warehouses | NO | | |
| subtotal | decimal(15,2) | NO | 0 | |
| discount_amount | decimal(15,2) | NO | 0 | |
| discount_type | enum('percentage','fixed') | YES | NULL | |
| discount_value | decimal(15,2) | YES | NULL | |
| tax_amount | decimal(15,2) | NO | 0 | |
| tax_rate | decimal(5,2) | YES | NULL | |
| shipping_cost | decimal(15,2) | NO | 0 | |
| other_charges | decimal(15,2) | NO | 0 | |
| total | decimal(15,2) | NO | 0 | |
| paid | decimal(15,2) | NO | 0 | |
| payment_status | enum('unpaid','partial','paid') | NO | 'unpaid' | |
| status | enum('draft','confirmed','cancelled') | NO | 'draft' | |
| notes | text | YES | NULL | |
| created_by | bigint FK users | YES | NULL | |
| updated_by | bigint FK users | YES | NULL | |
| timestamps + softDeletes | | YES | | |

### 2.19 sales_invoice_items

| Column | Type | Nullable | Default |
|--------|------|----------|---------|
| id | bigint PK AI | NO | |
| sales_invoice_id | bigint FK sales_invoices | NO | |
| product_id | bigint FK products | NO | |
| selling_unit_id | bigint FK product_selling_units | YES | NULL |
| quantity | decimal(15,2) | NO | 0 |
| price | decimal(15,2) | NO | 0 |
| tax_rate | decimal(5,2) | NO | 0 |
| tax_amount | decimal(15,2) | NO | 0 |
| discount_amount | decimal(15,2) | NO | 0 |
| total | decimal(15,2) | NO | 0 |
| notes | text | YES | NULL |
| timestamps | | YES | NULL |

### 2.20 purchase_invoices

| Column | Type | Nullable | Default |
|--------|------|----------|---------|
| id | bigint PK AI | NO | |
| invoice_number | varchar(100) | NO | UNIQUE |
| invoice_date | date | NO | |
| supplier_id | bigint FK suppliers | NO | |
| warehouse_id | bigint FK warehouses | NO | |
| subtotal | decimal(15,2) | NO | 0 |
| discount | decimal(15,2) | NO | 0 |
| tax | decimal(15,2) | NO | 0 |
| total | decimal(15,2) | NO | 0 |
| paid | decimal(15,2) | NO | 0 |
| status | enum('pending','paid','cancelled') | NO | 'pending' |
| notes | text | YES | NULL |
| created_by | bigint | YES | NULL |
| timestamps + softDeletes | | YES | |

### 2.21 purchase_invoice_items

| Column | Type | Nullable | Default |
|--------|------|----------|---------|
| id | bigint PK AI | NO | |
| purchase_invoice_id | bigint FK purchase_invoices | NO | |
| product_id | bigint FK products | NO | |
| qty | decimal(15,2) | NO | 0 |
| price | decimal(15,2) | NO | 0 |
| total | decimal(15,2) | NO | 0 |
| timestamps | | YES | NULL |

### 2.22 sales_returns

| Column | Type | Nullable | Default |
|--------|------|----------|---------|
| id | bigint PK AI | NO | |
| return_number | varchar(100) | NO | UNIQUE |
| sales_invoice_id | bigint FK sales_invoices | NO | |
| return_date | date | NO | |
| total_return_amount | decimal(15,2) | NO | 0 |
| reason | text | YES | NULL |
| status | enum('pending','approved','rejected') | NO | 'pending' |
| notes | text | YES | NULL |
| created_by | bigint | YES | NULL |
| timestamps + softDeletes | | YES | |

### 2.23 sales_return_items

| Column | Type | Nullable | Default |
|--------|------|----------|---------|
| id | bigint PK AI | NO | |
| sales_return_id | bigint FK sales_returns | NO | |
| product_id | bigint FK products | NO | |
| quantity | decimal(15,2) | NO | 0 |
| price | decimal(15,2) | NO | 0 |
| total | decimal(15,2) | NO | 0 |
| reason | text | YES | NULL |
| timestamps | | YES | NULL |

### 2.24 purchase_returns

| Column | Type | Nullable | Default |
|--------|------|----------|---------|
| id | bigint PK AI | NO | |
| return_number | varchar(100) | NO | UNIQUE |
| purchase_invoice_id | bigint FK purchase_invoices | YES | NULL |
| supplier_id | bigint FK suppliers | NO | |
| return_date | date | NO | |
| total_return_amount | decimal(15,2) | NO | 0 |
| reason | text | YES | NULL |
| status | enum('pending','approved','rejected','cancelled') | NO | 'pending' |
| notes | text | YES | NULL |
| created_by | bigint | YES | NULL |
| timestamps + softDeletes | | YES | |

### 2.25 purchase_return_items

| Column | Type | Nullable | Default |
|--------|------|----------|---------|
| id | bigint PK AI | NO | |
| purchase_return_id | bigint FK purchase_returns | NO | |
| product_id | bigint FK products | NO | |
| quantity | decimal(15,2) | NO | 0 |
| price | decimal(15,2) | NO | 0 |
| total | decimal(15,2) | NO | 0 |
| original_qty | decimal(15,2) | YES | NULL |
| reason | text | YES | NULL |
| timestamps | | YES | NULL |

### 2.26 warehouse_transfers

| Column | Type | Nullable | Default |
|--------|------|----------|---------|
| id | bigint PK AI | NO | |
| transfer_number | varchar(100) | NO | UNIQUE |
| from_warehouse_id | bigint FK warehouses | NO | |
| to_warehouse_id | bigint FK warehouses | NO | |
| transfer_date | date | NO | |
| status | enum('draft','processing','sent','received','cancelled','failed') | NO | 'draft' |
| notes | text | YES | NULL |
| created_by | bigint | YES | NULL |
| confirmed_by | bigint | YES | NULL |
| confirmed_at | timestamp | YES | NULL |
| received_by | bigint | YES | NULL |
| received_date | timestamp | YES | NULL |
| processing_started_at | timestamp | YES | NULL |
| processing_completed_at | timestamp | YES | NULL |
| processing_progress | decimal(5,2) | YES | NULL |
| last_processed_at | timestamp | YES | NULL |
| error_message | text | YES | NULL |
| failed_at | timestamp | YES | NULL |
| cancelled_by | bigint | YES | NULL |
| cancelled_at | timestamp | YES | NULL |
| cancellation_reason | text | YES | NULL |
| timestamps | | YES | NULL |

### 2.27 warehouse_transfer_items

| Column | Type | Nullable | Default |
|--------|------|----------|---------|
| id | bigint PK AI | NO | |
| warehouse_transfer_id | bigint FK warehouse_transfers | NO | |
| product_id | bigint FK products | NO | |
| quantity_sent | decimal(15,2) | NO | 0 |
| quantity_received | decimal(15,2) | YES | NULL |
| notes | text | YES | NULL |
| timestamps | | YES | NULL |

### 2.28 warehouse_inbound_orders

| Column | Type | Nullable | Default |
|--------|------|----------|---------|
| id | bigint PK AI | NO | |
| order_number | varchar(100) | NO | UNIQUE |
| warehouse_id | bigint FK warehouses | NO | |
| order_date | date | NO | |
| status | enum('pending','completed','cancelled') | NO | 'pending' |
| notes | text | YES | NULL |
| created_by | bigint | YES | NULL |
| timestamps | | YES | NULL |

### 2.29 warehouse_inbound_order_items

| Column | Type | Nullable | Default |
|--------|------|----------|---------|
| id | bigint PK AI | NO | |
| warehouse_inbound_order_id | bigint FK | NO | |
| product_id | bigint FK products | NO | |
| quantity | decimal(15,2) | NO | 0 |
| unit_price | decimal(15,2) | NO | 0 |
| total | decimal(15,2) | NO | 0 |
| notes | text | YES | NULL |
| timestamps | | YES | NULL |

### 2.30 warehouse_outbound_orders

| Column | Type | Nullable | Default |
|--------|------|----------|---------|
| id | bigint PK AI | NO | |
| order_number | varchar(100) | NO | UNIQUE |
| warehouse_id | bigint FK warehouses | NO | |
| order_date | date | NO | |
| purpose | enum('sale','transfer','return','damage','sample','other') | NO | 'sale' |
| status | enum('pending','completed','cancelled') | NO | 'pending' |
| notes | text | YES | NULL |
| approved_by | bigint | YES | NULL |
| approved_at | timestamp | YES | NULL |
| created_by | bigint | YES | NULL |
| timestamps | | YES | NULL |

### 2.31 warehouse_outbound_order_items

| Column | Type | Nullable | Default |
|--------|------|----------|---------|
| id | bigint PK AI | NO | |
| warehouse_outbound_order_id | bigint FK | NO | |
| product_id | bigint FK products | NO | |
| quantity | decimal(15,2) | NO | 0 |
| unit_price | decimal(15,2) | NO | 0 |
| total | decimal(15,2) | NO | 0 |
| notes | text | YES | NULL |
| timestamps | | YES | NULL |

### 2.32 stock_counts

| Column | Type | Nullable | Default |
|--------|------|----------|---------|
| id | bigint PK AI | NO | |
| count_number | varchar(100) | NO | UNIQUE |
| warehouse_id | bigint FK warehouses | NO | |
| count_type | enum('full','partial') | NO | 'full' |
| status | enum('draft','counting','completed','approved','cancelled') | NO | 'draft' |
| count_date | date | NO | |
| notes | text | YES | NULL |
| started_at | timestamp | YES | NULL |
| completed_at | timestamp | YES | NULL |
| approved_by | bigint | YES | NULL |
| approved_at | timestamp | YES | NULL |
| cancelled_by | bigint | YES | NULL |
| cancelled_at | timestamp | YES | NULL |
| cancellation_reason | text | YES | NULL |
| created_by | bigint | YES | NULL |
| timestamps | | YES | NULL |

### 2.33 stock_count_items

| Column | Type | Nullable | Default |
|--------|------|----------|---------|
| id | bigint PK AI | NO | |
| stock_count_id | bigint FK stock_counts | NO | |
| product_id | bigint FK products | NO | |
| system_quantity | decimal(15,2) | NO | 0 |
| counted_quantity | decimal(15,2) | YES | NULL |
| difference | decimal(15,2) | YES | NULL |
| adjustment_approved | boolean | NO | false |
| approved_by | bigint | YES | NULL |
| approved_at | timestamp | YES | NULL |
| notes | text | YES | NULL |
| timestamps | | YES | NULL |

### 2.34 inventory_movements

| Column | Type | Nullable | Default |
|--------|------|----------|---------|
| id | bigint PK AI | NO | |
| warehouse_id | bigint FK warehouses | NO | |
| product_id | bigint FK products | NO | |
| movement_type | varchar(50) | NO | |
| quantity | decimal(15,2) | NO | 0 |
| quantity_change | decimal(15,2) | NO | 0 |
| quantity_before | decimal(15,2) | YES | NULL |
| quantity_after | decimal(15,2) | YES | NULL |
| notes | text | YES | NULL |
| reference_type | varchar(255) | YES | NULL | morphTo
| reference_id | bigint | YES | NULL | morphTo
| movement_date | date | NO | |
| movement_number | varchar(100) | YES | NULL |
| created_by | bigint | YES | NULL |
| timestamps | | YES | NULL |

**Movement types:** `in`, `out`, `transfer_in`, `transfer_out`, `adjustment`, `return_in`, `return_out`, `sale`, `purchase`, `manufacturing`

### 2.35 cash_transactions

| Column | Type | Nullable | Default |
|--------|------|----------|---------|
| id | bigint PK AI | NO | |
| transaction_type | enum('deposit','withdrawal') | NO | |
| amount | decimal(15,2) | NO | 0 |
| description | text | YES | NULL |
| category | varchar(100) | YES | NULL |
| reference | varchar(255) | YES | NULL |
| transaction_date | date | NO | |
| created_by | bigint | YES | NULL |
| timestamps | | YES | NULL |

### 2.36 payments

| Column | Type | Nullable | Default |
|--------|------|----------|---------|
| id | bigint PK AI | NO | |
| payable_type | varchar(255) | NO | | polymorphic
| payable_id | bigint | NO | | polymorphic
| amount | decimal(15,2) | NO | 0 |
| payment_method | enum('cash','bank_transfer','check','card') | NO | |
| payment_date | date | NO | |
| reference_number | varchar(100) | YES | NULL |
| notes | text | YES | NULL |
| created_by | bigint | YES | NULL |
| timestamps | | YES | NULL |

### 2.37 expenses

| Column | Type | Nullable | Default |
|--------|------|----------|---------|
| id | bigint PK AI | NO | |
| expense_category_id | bigint FK expense_categories | YES | NULL |
| amount | decimal(15,2) | NO | 0 |
| description | text | YES | NULL |
| expense_date | date | NO | |
| payment_method | enum('cash','bank','card') | NO | 'cash' |
| reference | varchar(255) | YES | NULL |
| created_by | bigint | YES | NULL |
| timestamps | | YES | NULL |

### 2.38 expense_categories

| Column | Type | Nullable | Default |
|--------|------|----------|---------|
| id | bigint PK AI | NO | |
| name | varchar(255) | NO | |
| description | text | YES | NULL |
| timestamps | | YES | NULL |

### 2.39 supplier_payments

| Column | Type | Nullable | Default |
|--------|------|----------|---------|
| id | bigint PK AI | NO | |
| supplier_id | bigint FK suppliers | NO | |
| payment_number | varchar(100) | NO | UNIQUE |
| amount | decimal(15,2) | NO | 0 |
| payment_method | enum('cash','bank_transfer','check') | NO | |
| payment_date | date | NO | |
| reference | varchar(255) | YES | NULL |
| notes | text | YES | NULL |
| created_by | bigint | YES | NULL |
| timestamps | | YES | NULL |

### 2.40 supplier_product_prices

| Column | Type | Nullable | Default |
|--------|------|----------|---------|
| id | bigint PK AI | NO | |
| supplier_id | bigint FK suppliers | NO | |
| product_id | bigint FK products | NO | |
| price | decimal(15,2) | NO | 0 |
| min_order_quantity | decimal(15,2) | YES | NULL |
| is_preferred | boolean | NO | false |
| last_quoted_at | timestamp | YES | NULL |
| timestamps | | YES | NULL |

### 2.41 manufacturing_costs

| Column | Type | Nullable | Default |
|--------|------|----------|---------|
| id | bigint PK AI | NO | |
| product_id | bigint FK products | YES | NULL |
| cost_name | varchar(255) | NO | |
| status | enum('draft','confirmed') | NO | 'draft' |
| total_material_cost | decimal(15,4) | NO | 0 |
| waste_cost | decimal(15,4) | NO | 0 |
| labor_cost | decimal(15,4) | NO | 0 |
| nails_cost | decimal(15,4) | NO | 0 |
| tips_cost | decimal(15,4) | NO | 0 |
| transport_cost | decimal(15,4) | NO | 0 |
| fumigation_cost | decimal(15,4) | NO | 0 |
| total_additional_cost | decimal(15,4) | NO | 0 |
| total_cost | decimal(15,4) | NO | 0 |
| cost_per_unit | decimal(15,4) | NO | 0 |
| profit_margin | decimal(5,2) | NO | 0 |
| profit_amount | decimal(15,4) | NO | 0 |
| selling_price | decimal(15,4) | NO | 0 |
| notes | text | YES | NULL |
| created_by | bigint | YES | NULL |
| updated_by | bigint | YES | NULL |
| timestamps + softDeletes | | YES | |

### 2.42 bom_components (Bill of Materials)

| Column | Type | Nullable | Default |
|--------|------|----------|---------|
| id | bigint PK AI | NO | |
| manufacturing_cost_id | bigint FK manufacturing_costs | NO | |
| component_type | enum('raw_material','hardware','other') | NO | |
| component_name | varchar(255) | NO | |
| dimensions | varchar(255) | YES | NULL |
| thickness_cm | decimal(10,2) | YES | NULL |
| width_cm | decimal(10,2) | YES | NULL |
| length_cm | decimal(10,2) | YES | NULL |
| quantity | decimal(15,2) | NO | 1 |
| price_per_cubic_meter | decimal(15,4) | NO | 0 |
| component_cost | decimal(15,4) | NO | 0 |
| notes | text | YES | NULL |
| timestamps | | YES | NULL |

### 2.43 manufacturing_orders

| Column | Type | Nullable | Default |
|--------|------|----------|---------|
| id | bigint PK AI | NO | |
| order_number | varchar(100) | NO | UNIQUE |
| product_id | bigint FK products | YES | NULL |
| product_name | varchar(255) | NO | |
| quantity_produced | decimal(15,2) | NO | 0 |
| cost_per_unit | decimal(15,4) | NO | 0 |
| total_cost | decimal(15,4) | NO | 0 |
| selling_price_per_unit | decimal(15,4) | NO | 0 |
| waste_cost | decimal(15,4) | NO | 0 |
| labor_cost | decimal(15,4) | NO | 0 |
| nails_cost | decimal(15,4) | NO | 0 |
| tips_cost | decimal(15,4) | NO | 0 |
| transport_cost | decimal(15,4) | NO | 0 |
| fumigation_cost | decimal(15,4) | NO | 0 |
| profit_margin | decimal(5,2) | NO | 0 |
| profit_amount | decimal(15,4) | NO | 0 |
| warehouse_id | bigint FK warehouses | YES | NULL |
| status | enum('draft','confirmed','completed','cancelled') | NO | 'draft' |
| confirmed_by | bigint | YES | NULL |
| confirmed_at | timestamp | YES | NULL |
| completed_by | bigint | YES | NULL |
| completed_at | timestamp | YES | NULL |
| cancelled_by | bigint | YES | NULL |
| cancelled_at | timestamp | YES | NULL |
| cancellation_reason | text | YES | NULL |
| notes | text | YES | NULL |
| created_by | bigint | YES | NULL |
| updated_by | bigint | YES | NULL |
| timestamps + softDeletes | | YES | |

### 2.44 manufacturing_order_components

| Column | Type | Nullable | Default |
|--------|------|----------|---------|
| id | bigint PK AI | NO | |
| manufacturing_order_id | bigint FK manufacturing_orders | NO | |
| component_type | enum('raw_material','hardware','other') | NO | |
| component_name | varchar(255) | NO | |
| thickness_cm | decimal(10,2) | YES | NULL |
| width_cm | decimal(10,2) | YES | NULL |
| length_cm | decimal(10,2) | YES | NULL |
| quantity | decimal(15,2) | NO | 1 |
| price_per_cubic_meter | decimal(15,4) | NO | 0 |
| component_cost | decimal(15,4) | NO | 0 |
| notes | text | YES | NULL |
| timestamps | | YES | NULL |

### 2.45 unit_conversions

| Column | Type | Nullable | Default |
|--------|------|----------|---------|
| id | bigint PK AI | NO | |
| product_id | bigint FK products | NO | |
| from_unit | varchar(50) | NO | |
| to_unit | varchar(50) | NO | |
| conversion_factor | decimal(15,4) | NO | 1 |
| is_active | boolean | NO | true |
| timestamps | | YES | NULL |

### 2.46 companies

| Column | Type | Nullable | Default |
|--------|------|----------|---------|
| id | bigint PK AI | NO | |
| name | varchar(255) | NO | |
| phone | varchar(20) | YES | NULL |
| email | varchar(255) | YES | NULL |
| address | text | YES | NULL |
| tax_number | varchar(100) | YES | NULL |
| commercial_register | varchar(100) | YES | NULL |
| logo | varchar(255) | YES | NULL |
| timestamps | | YES | NULL |

### 2.47 system_settings

| Column | Type | Nullable | Default |
|--------|------|----------|---------|
| id | bigint PK AI | NO | |
| key | varchar(100) | NO | UNIQUE |
| value | text | YES | NULL |
| timestamps | | YES | NULL |

### 2.48 activity_logs

| Column | Type | Nullable | Default |
|--------|------|----------|---------|
| id | bigint PK AI | NO | |
| user_id | bigint FK users | YES | NULL |
| action | varchar(255) | NO | |
| model_type | varchar(255) | YES | NULL |
| model_id | bigint | YES | NULL |
| properties | json | YES | NULL |
| created_at | timestamp | YES | NULL |

### 2.49 notifications (Laravel standard)

Standard Laravel notifications table.

---

## 3. Models - Complete

### 3.1 User (app/Models/User.php)

**Traits:** HasFactory, Notifiable, SoftDeletes

**Fillable:** name, email, password, phone, is_active, role

**Hidden:** password, remember_token

**Casts:** email_verified_at => datetime, is_active => boolean

**Methods:**
- `isAdmin(): bool` - checks `$this->role === 'admin'`
- `isEmployee(): bool` - checks `$this->role === 'employee'`
- `getRoleNameAttribute(): string` - returns Arabic role name ('مدير' or 'موظف')
- `allPermissions(): array` - merges direct permissions + role permissions, returns unique array of permission names
- `hasPermission(string $permission): bool` - admin always true; otherwise checks against allPermissions()
- `hasAnyPermission(array $permissions): bool` - admin always true; otherwise checks intersection
- `hasAllPermissions(array $permissions): bool` - admin always true; otherwise checks diff
- `givePermissionTo(Permission $permission)` - attaches to permission_user pivot
- `revokePermissionFrom(Permission $permission)` - detaches from permission_user pivot
- `syncPermissions(array $permissions)` - syncs permission_user pivot
- `assignRole(Role $role)` - attaches to role_user pivot
- `removeRole(Role $role)` - detaches from role_user pivot
- `syncRoles(array $roleIds)` - syncs role_user pivot
- `hasRole(string $roleName): bool` - checks role_user pivot

**Relationships:**
- `roles()` - belongsToMany Role, pivot role_user, withTimestamps
- `permissions()` - belongsToMany Permission, pivot permission_user, withTimestamps
- `salesInvoices()` - hasMany SalesInvoice (created_by)
- `purchaseInvoices()` - hasMany PurchaseInvoice (created_by)
- `salesReturns()` - hasMany SalesReturn (created_by)
- `purchaseReturns()` - hasMany PurchaseReturn (created_by)
- `warehouseTransfers()` - hasMany WarehouseTransfer (created_by)
- `payments()` - hasMany Payment (created_by)
- `expenses()` - hasMany Expense (created_by)
- `cashTransactions()` - hasMany CashTransaction (created_by)
- `activities()` - hasMany ActivityLog (user_id)

**Scopes:** `scopeActive` - where is_active = true

### 3.2 Product (app/Models/Product.php)

**Traits:** HasFactory, SoftDeletes, ProductStockManagement

**Fillable:** name, code, sku, barcode, description, category_id, purchase_price, selling_price, min_stock, unit_type, image, is_active, product_type, is_manufactured

**Casts:** purchase_price/decimal:15,2, selling_price/decimal:15,2, is_active/boolean, is_manufactured/boolean

**Relationships:**
- `category()` - belongsTo Category
- `warehouses()` - belongsToMany Warehouse, pivot product_warehouse with quantity/min_stock/reserved_quantity
- `sellingUnits()` - hasMany ProductSellingUnit
- `baseUnits()` - hasMany ProductBaseUnit
- `basePricing()` - hasMany ProductBasePricing
- `priceHistory()` - hasMany ProductPriceHistory
- `priceChangeHistory()` - hasMany PriceChangeHistory
- `salesInvoiceItems()` - hasMany SalesInvoiceItem
- `purchaseInvoiceItems()` - hasMany PurchaseInvoiceItem
- `unitConversions()` - hasMany UnitConversion
- `supplierPrices()` - hasMany SupplierProductPrice
- `inventoryMovements()` - hasMany InventoryMovement
- `manufacturingCosts()` - hasMany ManufacturingCost

### 3.3 SalesInvoice (app/Models/SalesInvoice.php)

**Fillable:** invoice_number, invoice_date, due_date, customer_id, warehouse_id, subtotal, discount_amount, discount_type, discount_value, tax_amount, tax_rate, shipping_cost, other_charges, total, paid, payment_status, status, notes, created_by, updated_by

**Casts:** invoice_date/date, due_date/date, subtotal..total/decimal:15,2, paid/decimal:15,2

**Relationships:**
- `customer()` - belongsTo Customer
- `warehouse()` - belongsTo Warehouse
- `items()` - hasMany SalesInvoiceItem
- `payments()` - morphMany Payment
- `returns()` - hasMany SalesReturn
- `createdBy()` - belongsTo User (created_by)
- `updatedBy()` - belongsTo User (updated_by)

**Scopes:** paid, pending, cancelled, thisMonth, today

**Methods:**
- `calculateTotals()` - sums items, applies discount/tax/shipping/other, sets total
- `generateInvoiceNumber()` - static, format: SI-YYYYMMDD-NNNN

### 3.4 PurchaseInvoice, Customer, Supplier, Warehouse, etc.

All follow the same pattern as above. Full model details are preserved from the original codebase. Each model retains its exact fillable, casts, relationships, scopes, and custom methods.

---

## 4. Authorization System

### 4.1 Dual Authorization Architecture

The system uses TWO coexisting authorization systems:

**System A: Simple Role Column**
- `users.role` column: enum('admin', 'employee')
- `isAdmin()` checks `$this->role === 'admin'`
- Used by: RoleMiddleware, AdminOnly middleware, ProductPolicy

**System B: Full RBAC (Pivot Tables)**
- `role_user`, `permission_role`, `permission_user` pivot tables
- Used by: AuthServiceProvider gates, PermissionsController
- Admin bypasses all gates via `Gate::before`

### 4.2 Middleware

**RoleMiddleware** (app/Http/Middleware/RoleMiddleware.php):
1. Check user is authenticated
2. Check `is_active` is true (logout if false)
3. Default role to 'employee' if null
4. Admins bypass all checks
5. Employees blocked from these route prefixes: accounting, reports, settings, dashboard, warehouses, stock-counts, manufacturing
6. Blocked employees redirected to `invoices.sales.index` with error

**AdminOnly** (app/Http/Middleware/AdminOnly.php):
1. Check authenticated, redirect to login if not
2. Check `isAdmin()`, return 403 JSON if not
3. Pass through if admin

### 4.3 Gates (AuthServiceProvider)

```php
Gate::before(fn ($user) => $user->isAdmin()); // Admins bypass ALL gates

Gate::define('warehouse.transfers.read', fn ($user) => $user->hasPermission('warehouse.transfers.read'));
Gate::define('warehouse.transfers.create', fn ($user) => $user->hasPermission('warehouse.transfers.create'));
Gate::define('warehouse.transfers.update', fn ($user) => $user->hasPermission('warehouse.transfers.update'));
Gate::define('warehouse.transfers.delete', fn ($user) => $user->hasPermission('warehouse.transfers.delete'));
Gate::define('users.permissions', fn ($user) => $user->isAdmin() || $user->hasPermission('users.permissions'));
```

### 4.4 Policies

**ProductPolicy:**
- viewAny: true (all authenticated)
- view: true
- create/update/delete: $user->isAdmin()

**SettingsPolicy:**
- viewAny: true
- updateCompany: user has admin or manager role via pivot
- updateSystem/manageUsers/manageBackup: user has admin role via pivot

### 4.5 Filament Authorization Mapping

In Filament, these translate to:

| Current System | Filament Equivalent |
|---------------|---------------------|
| `RoleMiddleware` | Custom FilamentMiddleware registered in panel config |
| `AdminOnly` middleware | `->authGuard('web')` + canAccess() on panel |
| Gates | `->authorizeGate()` on Resources, or custom `can()` methods |
| ProductPolicy | Register with `->modelPolicy()` or use `->can()` on Resource |
| Permission checks in controllers | `->visible()` and `->disabled()` on Filament Actions |

### 4.6 49 Permissions across 10 Modules

**sales (8):** invoices.create, invoices.read, invoices.update, invoices.delete, invoices.print, returns.create, returns.read, returns.delete

**purchases (7):** invoices.create, invoices.read, invoices.update, invoices.delete, returns.create, returns.read, returns.delete

**warehouse (8):** products.create, products.read, products.update, products.delete, transfers.create, transfers.read, movements.read, stock_counts.create

**customers (5):** create, read, update, delete, statement

**suppliers (5):** create, read, update, delete, statement

**manufacturing (5):** create, read, update, delete, complete

**reports (5):** sales, purchases, inventory, financial, profit_loss

**accounting (4):** treasury, payments, expenses, statistics

**users (5):** create, read, update, delete, permissions

**settings (2):** company, system

**Employee defaults (14):** sales.invoices.read, sales.invoices.create, sales.invoices.update, sales.returns.read, purchases.invoices.read, purchases.invoices.create, purchases.returns.read, warehouse.products.read, warehouse.transfers.read, warehouse.movements.read, customers.read, suppliers.read, manufacturing.read, reports.sales, reports.purchases, reports.inventory

---

## 5. Authentication Flow

### 5.1 Login (LoginController)

1. Validate email (required|email) and password (required|string)
2. Find user by email, return "بيانات الدخول غير صحيحة" if not found
3. Check `$user->is_active`, return "الحساب غير مفعل" if false
4. `Auth::attempt()` with email+password, optional remember
5. On success: regenerate session, redirect to intended URL or `/` (dashboard)
6. On failure: redirect back with error "بيانات الدخول غير صحيحة"

### 5.2 Logout

1. `Auth::logout()`
2. Invalidate session
3. Regenerate CSRF token
4. Redirect to login

### 5.3 Registration - DISABLED

Both `showRegistrationForm()` and `register()` redirect to login with "التسجيل غير متاح حالياً". Only admins create users through User Management.

### 5.4 Filament Auth Setup

Filament panel should use the existing `web` guard. The `is_active` check must be added to Filament's `authenticate()` method or via a custom credentials validator.

---

## 6. Event/Listener System

### 6.1 Event-Listener Map (from EventServiceProvider)

| Event | Listeners |
|-------|-----------|
| `SalesInvoiceCreated` | SendSalesInvoiceCreatedNotification |
| `SalesInvoiceConfirmed` | UpdateSalesInvoiceConfirmedCache |
| `SalesInvoiceCancelled` | HandleSalesInvoiceCancellation |
| `PurchaseInvoiceCreated` | SendPurchaseInvoiceCreatedNotification |
| `PurchaseInvoiceCancelled` | HandlePurchaseInvoiceCancellation |
| `PaymentReceived` | HandlePaymentReceived, UpdateInvoicePaymentStatus, UpdateCustomerOrSupplierBalance, RecordInAccountingLedger, LogPaymentActivity |
| `PaymentCancelled` | HandlePaymentCancellation |
| `SalesReturnProcessed` | HandleSalesReturnProcessed |
| `PurchaseReturnProcessed` | HandlePurchaseReturnProcessed |
| `StockLow` | SendLowStockAlert |
| `StockUpdated` | UpdateStockCache |
| `TransferInitiated` | SendTransferInitiatedNotification |
| `TransferCompleted` | HandleTransferCompleted |
| `TransferCancelled` | HandleTransferCancellation |
| `TransferReversed` | HandleTransferReversal |

### 6.2 Key Listener Logic

**HandleSalesInvoiceCancellation:**
- Restores stock for each item (increment product_warehouse quantities)
- Updates customer balance (decrement)
- Logs activity

**HandlePurchaseInvoiceCancellation:**
- Deducts stock for each item (decrement product_warehouse quantities)
- Updates supplier balance (decrement)
- Logs activity

**HandlePaymentReceived:**
- Creates payment record
- Updates invoice paid amount and payment_status
- Updates customer/supplier balance
- Records in accounting ledger (CashTransaction)

**HandleSalesReturnProcessed:**
- Increments product_warehouse stock for returned items
- Updates customer balance (decrement)

**HandlePurchaseReturnProcessed:**
- Decrements product_warehouse stock for returned items
- Updates supplier balance (increment)

**SendLowStockAlert:**
- Sends LowStockNotification to admin users

**HandleTransferCompleted:**
- Moves stock from source warehouse to destination warehouse
- Creates inventory movements (transfer_out, transfer_in)
- Fires TransferCompletedNotification

**HandleTransferCancellation:**
- Reverts stock movements if transfer was already processed

**HandleTransferReversal:**
- Reverses stock movements (moves stock back to source warehouse)

### 6.3 Observer: ProductBaseUnitObserver

On `ProductBaseUnit::updated`:
1. Update `products.purchase_price` and `products.selling_price` to match new base unit prices
2. If `auto_update_selling_units` is true:
   - Update the base selling unit's prices
   - Update all non-base selling units where `auto_calculate_price = true` using `conversion_factor`

### 6.4 Filament Impact

**CRITICAL:** All events/listeners/observers remain unchanged. They are triggered by Eloquent model events, which Filament uses. When a Filament Resource creates/updates/deletes a model, the same events fire. The only thing changing is the UI layer - the event system stays exactly the same.

---

## 7. Service Layer - Complete Business Logic

### 7.1 InvoiceService (1524 lines)

**Constructor:** `ProductService, CustomerService, SupplierService`

**Sales Invoice Methods:**

`getSalesInvoicesWithFilters(Request $request):`
- Filters: search (invoice_number, customer name/phone/email), customer_id, warehouse_id, status (paid/pending/cancelled), date_from/date_to, amount_min/amount_max
- Sort: by invoice_number, invoice_date, total, paid, created_at, customer_id, warehouse_id
- Eager loading: customer (id,name,phone,balance), warehouse (id,name,code), createdBy (id,name)
- Calculates `remaining = total - paid` per invoice
- Paginated (default 20)

`getSalesStatisticsWithFilters(Request $request):`
- Same filters as above
- Returns: total_invoices, total_amount, paid_amount, pending_amount, cancelled_count, today_sales, today_count, month_sales, month_count

`createSalesInvoice(array $data): SalesInvoice:`
- DB transaction
- Generate invoice number: SI-YYYYMMDD-NNNN
- Create invoice record
- For each item: create SalesInvoiceItem with product_id, selling_unit_id, quantity, price, tax_rate, tax_amount, discount_amount, total
- Calculate totals: subtotal = sum(items.total), apply discount (percentage or fixed), apply tax, shipping, other charges -> total
- Update customer balance: increment by total
- Update product warehouse stock: decrement quantity for each item
- Fire `SalesInvoiceCreated` event
- Log activity

`updateSalesInvoice($id, array $data):`
- DB transaction
- Reverse previous stock movements (restore quantities)
- Reverse previous customer balance change
- Delete old items
- Recreate items and recalculate totals (same logic as create)
- Update customer balance with new total
- Update product warehouse stock with new quantities

`cancelSalesInvoice($id):`
- Check status is not already cancelled
- Set status = 'cancelled'
- Restore stock (increment product_warehouse for each item)
- Decrement customer balance by total
- Fire `SalesInvoiceCancelled` event

`calculateInvoiceDetails(SalesInvoice $invoice): array:`
- Returns: subtotal, total_discount, total_tax, shipping, other_charges, total, paid, remaining, items_count

`getSalesInvoiceForPrint($id):`
- Loads invoice with all relationships + company data for PDF printing

**Purchase Invoice Methods:**

`getPurchaseInvoicesWithFilters(Request $request):`
- Same filter pattern as sales

`getPurchaseStatisticsWithFilters(Request $request):`
- Same statistics pattern as sales

`createPurchaseInvoice(array $data): PurchaseInvoice:`
- Similar to sales but for purchases
- Invoice number format: PI-YYYYMMDD-NNNN
- Increments stock (product_warehouse) instead of decrementing
- Updates supplier balance instead of customer balance
- Fire `PurchaseInvoiceCreated` event

`updatePurchaseInvoice($id, array $data):`
- Same reverse-and-recreate pattern

`cancelPurchaseInvoice($id):`
- Reverse stock movements (decrement)
- Update supplier balance
- Fire `PurchaseInvoiceCancelled` event

### 7.2 TransferService (865 lines)

**Uses:** TransferValidationTrait

**Constants:** CHUNK_SIZE=500, BATCH_INSERT_SIZE=1000, CACHE_TTL=300

**Constructor:** `InventoryMovementService`

`getAllTransfers(array $filters):`
- Filters: from_warehouse, to_warehouse, status, date_from, date_to, search (transfer_number)
- Eager loading: fromWarehouse, toWarehouse, items.product, createdBy
- WithCount: items as total_items
- Subselect: total_quantity_sent, total_quantity_received
- Paginated

`createTransfer(array $data): WarehouseTransfer:`
- Validates items array not empty
- Validates from_warehouse != to_warehouse
- DB transaction:
  - Generate transfer number: TR-YYYYMMDD-NNNN
  - Create WarehouseTransfer record
  - For each item: create WarehouseTransferItem
  - Process stock movements immediately:
    - For each item: decrement source warehouse, increment destination warehouse
    - Create inventory movements (transfer_out, transfer_in)
  - Set status = 'received'
  - Clear cache for both warehouses

`reverseTransfer($id): WarehouseTransfer:`
- DB transaction
- Validates status allows reversal
- For each item: decrement destination warehouse, increment source warehouse (reverse)
- Create inventory movements (transfer_in for source, transfer_out for dest - reversed)
- Set status = 'cancelled'
- Fire `TransferReversed` event

`cancelTransfer($id): WarehouseTransfer:`
- Validates status allows cancellation
- If already processed: reverse stock movements first
- Set status = 'cancelled'
- Fire `TransferCancelled` event

**TransferValidationTrait (82 lines):**
- `validateTransferData(array $data): void` - checks warehouses differ, items exist
- `validateTransferItems(array $items, int $fromWarehouseId): void` - checks sufficient stock, valid products
- `validateTransferStatus(WarehouseTransfer $transfer, string $action): void` - checks status allows action

### 7.3 ProductService (1224 lines)

**Constants:**
- UNITS_BY_CATEGORY: weight (kg,g,ton,lb), length (m,cm,mm,km), volume (l,ml,m3), quantity (piece,box,carton,bag,pack,dozen), area (m2,cm2)
- UNIT_LABELS: Arabic labels for all units
- UNIT_TYPE_MAP: maps unit codes to their type

`createProduct(array $data): Product:`
- DB transaction
- Generate SKU if not provided
- Create product record
- Create base units from $data['base_units'] array
- Create selling units from $data['selling_units'] array
- Attach to warehouses with initial quantities
- Create ProductBasePricing records
- Create unit conversions
- Log initial stock via InventoryMovementService

`updateProduct($id, array $data): Product:`
- DB transaction
- Update product record
- Sync base units (delete old, create new)
- Sync selling units
- Sync warehouse attachments
- Sync base pricing
- Sync unit conversions

`updateStock(int $productId, int $warehouseId, float $quantity, string $movementType = 'adjustment'):`
- DB transaction
- Get or create ProductWarehouse record
- Update quantity
- Create InventoryMovement record

`getUnitsByCategory(): array` - returns UNITS_BY_CATEGORY constant

`getProductsByUnitAndCategory(string $baseUnit, ?int $categoryId):`
- Queries products by unit_type matching base unit category
- Optional category filter

`getSuggestedPricing(int $productId): array:`
- Returns suggested selling prices based on cost and margins

### 7.4 AccountingService (275 lines)

`getCashBalance(): float:`
- deposits sum - withdrawals sum

`getBankBalance(): float:` - returns 0.00 (not implemented)

`addDeposit(amount, description, category, date, reference): CashTransaction:`
- DB transaction
- Creates CashTransaction with TYPE_DEPOSIT

`addWithdrawal(amount, description, category, date, reference): CashTransaction:`
- DB transaction
- Checks balance >= amount (throws if insufficient)
- Creates CashTransaction with TYPE_WITHDRAWAL

`getTodayTransactions():` - CashTransaction where transaction_date = today

`getCategories():` - distinct categories from cash_transactions

`getCashStatistics():` - returns today_deposits, today_withdrawals, today_net, month_deposits, month_withdrawals, month_net

`getTransactionsByType(string $type, array $filters):` - paginated filterable

`updateTransaction($id, array $data):` - updates CashTransaction

`deleteTransaction($id):` - deletes CashTransaction

### 7.5 ManufacturingCostService (212 lines)

`createCost(array $data): ManufacturingCost:`
- Create ManufacturingCost record
- Create BomComponent records for each component
- Calculate component costs: volume = quantity x thickness x width x length, cost = (volume / 1000000) x price_per_cubic_meter
- Sum all component costs -> total_material_cost
- Calculate total_additional_cost = waste + labor + nails + tips + transport + fumigation
- Calculate total_cost, cost_per_unit, profit, selling_price

`updateCost($id, array $data):` - recalculate all costs

`confirmCost($id):` - set status = 'confirmed'

`calculateCosts(array $components, array $additionalCosts): array:`
- AJAX endpoint logic
- Returns calculated costs breakdown

### 7.6 ManufacturingOrderService (446 lines)

**Constructor:** `InventoryMovementService, ProductService`

`createOrder(array $data): ManufacturingOrder:`
- DB transaction
- Calculate components total: for each component, volume = quantity x thickness x width x length, cost = (volume / 1000000) x price_per_cubic_meter
- Calculate additional costs: waste + labor + nails + tips + transport + fumigation
- cost_per_unit = components_total + additional_total
- profit_per_unit = cost_per_unit x (profit_margin / 100)
- selling_price_per_unit = cost_per_unit + profit_per_unit
- total_cost = cost_per_unit x quantity_produced
- total_profit = profit_per_unit x quantity_produced
- Create ManufacturingOrder
- Create ManufacturingOrderComponent records

`confirmOrder($id):` - status -> 'confirmed'

`completeOrder($id, array $data):`
- DB transaction
- Find or create product using `firstOrCreate()` with product_name
- Update product prices from manufacturing order
- Update/create ProductWarehouse record (increment stock)
- Create InventoryMovement (type: manufacturing)
- Set status = 'completed', completed_by, completed_at

`cancelOrder($id, string $reason):`
- Set status = 'cancelled', cancelled_by, cancelled_at, cancellation_reason

`calculateCosts(array $data): array:`
- Same calculation logic as createOrder but returns array without creating

### 7.7 StockCountService (652 lines)

**Uses:** ManagesStockCountStatus, OptimizesStockCountQueries

**Constructor:** `InventoryMovementService`

`getCreateData(int $warehouseId): array:`
- Get warehouse with products and their stock levels
- Returns warehouse, products with system_quantity

`createStockCount(array $data): StockCount:`
- Generate count number: SC-YYYYMMDD-NNNN
- Create StockCount record
- Create StockCountItem records with system_quantity from product_warehouse

`startCount($id):`
- Set status = 'counting', started_at = now()

`countItem($countId, $itemId, array $data): StockCountItem:`
- Update counted_quantity
- Calculate difference = counted_quantity - system_quantity
- If difference == 0: auto-approve

`approveItemAdjustment($countId, $itemId):`
- Set adjustment_approved = true, approved_by, approved_at

`approveAll($countId):`
- Approve all items with difference != 0

`completeCount($countId):`
- DB transaction
- For each approved item with difference:
  - Update product_warehouse quantity to counted_quantity
  - Create InventoryMovement (type: adjustment)
- Set status = 'completed', completed_at

`cancelCount($countId, string $reason):`
- Set status = 'cancelled', cancelled_by, cancelled_at, cancellation_reason

**ManagesStockCountStatus trait (105 lines):**
- `canTransitionTo(StockCount $count, string $newStatus): bool` - enforces valid transitions
- Valid transitions: draft->counting, counting->completed/approved/cancelled, draft->cancelled
- `getValidTransitions(string $status): array`

**OptimizesStockCountQueries trait (236 lines):**
- `optimizeCountItemsQuery(Builder $query): Builder` - eager loading optimization
- `getCountWithItems($id): StockCount` - optimized single query with items
- `getCountStatistics($id): array` - progress stats

### 7.8 CustomerService (184 lines)

`create(array $data): Customer:`
- DB transaction
- Generate customer code: CUST-NNNN
- Create customer
- Clear cache

`update($id, array $data): Customer:`
- Update customer fields
- Clear cache

`delete($id):`
- Soft delete
- Clear cache

`getCustomerStatement($id, array $filters): array:`
- Get sales invoices for customer with filters (date_from, date_to, status)
- Calculate totals: total_invoiced, total_paid, total_returns, balance
- Each transaction has: date, reference, type, debit, credit, running_balance

### 7.9 SupplierService (259 lines)

`create(array $data): Supplier:`
- DB transaction
- Generate code: SUP-NNNN
- Create supplier
- Clear cache

`update($id, array $data):` - same pattern

`delete($id):` - soft delete

`toggleStatus($id):` - toggle is_active

`getSupplierStatement($id, array $filters): array:`
- Union of purchase_invoices + supplier_payments + purchase_returns
- Running balance calculation
- Date and type filters

### 7.10 SupplierStatementService (135 lines)

`generateStatement(Supplier $supplier, array $filters = []): array:`
- DB::table union of purchase_invoices, supplier_payments, purchase_returns
- Calculates running balance
- Returns transactions + opening_balance + closing_balance

### 7.11 SupplierBalanceService (102 lines)

`updateSupplierBalance(Supplier $supplier): void:`
- total_purchases = sum of non-cancelled purchase invoices
- total_payments = sum of supplier payments
- total_returns = sum of non-cancelled purchase returns
- balance = total_purchases - total_payments - total_returns

### 7.12 WarehouseService (507 lines)

`createWarehouse(array $data): Warehouse:`
- Create warehouse
- Attach initial products if provided

`updateWarehouse($id, array $data): Warehouse:`

`deleteWarehouse($id):` - soft delete

`getWarehouseDetails($id): array:`
- Warehouse with products, stock levels, movements, stats

`addProductToWarehouse(int $warehouseId, array $data):`
- Create/update ProductWarehouse record

`removeProductFromWarehouse(int $warehouseId, int $productId):`
- Delete ProductWarehouse record

`getLowStockProducts(int $warehouseId):` - products where quantity <= min_stock

`getWarehouseStats($id): array:`
- total_products, total_value, low_stock_count

### 7.13 WarehouseStockService (313 lines)

`getWarehouseStock(int $warehouseId):` - all products with quantities

`getStockForProduct(int $productId, int $warehouseId):` - single product stock

`updateStock(int $productId, int $warehouseId, float $quantity):` - update product_warehouse

`transferStock(int $productId, int $fromWarehouseId, int $toWarehouseId, float $quantity):`
- Decrement source, increment destination
- Create inventory movements

`getAllWarehousesStock():` - stock summary across all warehouses

### 7.14 InventoryMovementService (429 lines)

`recordMovement(array $data): InventoryMovement:`
- Create movement record
- Generate movement number: MV-YYYYMMDD-HHMMSS-{productId}

`getMovements(array $filters):` - filterable paginated list

`getProductMovements(int $productId, array $filters = []):` - movements for one product

`getWarehouseMovements(int $warehouseId, array $filters = []):` - movements for one warehouse

`getMovementStats(array $filters): array:`
- total_in, total_out, net_change, most_moved_product

### 7.15 ReturnService (482 lines)

**Constructor:** `ProductService, CustomerService, SupplierService`

`createSalesReturn(array $data): SalesReturn:`
- DB transaction
- Generate return number: SRET-YYYYMMDD-NNNN
- Validate sales_invoice exists and is not cancelled
- Create SalesReturn
- Create SalesReturnItems
- Calculate total_return_amount
- Increment product_warehouse stock for returned items
- Update customer balance (decrement by return amount)
- Fire `SalesReturnProcessed` event

`cancelSalesReturn($id):`
- Reverse stock changes (decrement product_warehouse)
- Update customer balance (increment)
- Delete return

`createPurchaseReturn(array $data): PurchaseReturn:`
- Same pattern for purchases
- Decrements stock
- Updates supplier balance
- Return number: PRET-YYYYMMDD-NNNN

`getSalesReturnStatistics(array $filters): array:`
- total_returns, total_amount, pending, today

### 7.16 PurchaseInvoiceService (280 lines)

`createPurchaseInvoice(array $data): PurchaseInvoice:`
- Same as InvoiceService purchase logic
- Invoice number: PI-YYYYMMDD-NNNN

`updatePurchaseInvoice($id, array $data):`

`deletePurchaseInvoice($id):` - soft delete

### 7.17 PurchaseReturnService (324 lines)

`createPurchaseReturn(array $data): PurchaseReturn:`
- Generate PRET-YYYYMMDD-NNNN
- Validate purchase invoice exists
- Check available items (qty remaining to return)
- Create return with items
- Decrement stock, update supplier balance

`updatePurchaseReturn($id, array $data):`

`deletePurchaseReturn($id):`

`getAvailableItemsForInvoice($invoiceId):` - items with remaining qty to return

### 7.18 ReportingService (365 lines)

`dashboardSummary(): array:`
- today_sales, today_purchases, month_sales, month_purchases
- total_customers, total_suppliers, total_products
- cash_balance, recent_invoices, low_stock_products

`getFinancialReport(array $filters): array:`
- revenue (sales - returns), expenses, cogs
- gross_profit = revenue - cogs
- net_profit = gross_profit - expenses
- Top products by revenue, top customers
- Daily sales breakdown

`getInventoryReport(array $filters): array:`
- Products with stock levels, values
- Low stock alerts
- Stock valuation by warehouse
- Total inventory value

`getProfitLossReport(array $filters): array:`
- Revenue breakdown
- COGS breakdown
- Gross profit
- Operating expenses by category
- Net profit

### 7.19 AdvancedPricingService (707 lines)

**Uses:** UnitsManagement trait

`getCategoriesByBaseUnit(string $baseUnit):` - categories that have products with this unit type

`getProductsByUnitAndCategory(string $baseUnit, ?int $categoryId):` - filtered products

`applyBulkPriceUpdate(array $data):`
- DB transaction
- For each product: update selling price based on profit_type (percentage/fixed)
- Create PriceChangeHistory records
- Update ProductSellingUnit prices
- Clear cache

`previewSmartUpdate(array $data): array:`
- Preview price changes before applying

**UnitsManagement trait (432 lines):**
- `getAllUnits():` - returns units from database or defaults
- `getActiveUnits():` - units with active base pricing
- `getMostUsedUnits():` - most common units
- `getUnitsByCategory():` - grouped by type
- `getUnitLabel(string $code):` - Arabic label
- `getUnitType(string $code):` - weight/length/volume/quantity/area

### 7.20 PriceUpdateService (486 lines)

`bulkUpdatePrices(array $data):`
- DB transaction
- For each product: update base selling price
- Create ProductPriceHistory records
- Clear cache

`getPriceHistory(int $productId):` - price changes for a product

### 7.21 ProductPricingService (417 lines)

`calculateSellingPrice(int $productId, string $unitCode, float $purchasePrice, float $profitMargin): float:`
- Get conversion factor
- Calculate: (purchasePrice * factor) * (1 + profitMargin/100)

`getPriceForUnit(int $productId, string $unitCode): ?float:`
- Get current selling price for a specific unit

### 7.22 Accounting/ExpenseService (373 lines)

`createExpense(array $data): Expense:`
- Create expense record
- Create CashTransaction (withdrawal)

`updateExpense($id, array $data):`

`deleteExpense($id):` - also deletes related CashTransaction

`getExpenses(array $filters):` - filterable list

`getExpenseStatistics(array $filters): array:`
- today_total, month_total, year_total, by_category

### 7.23 Accounting/PaymentService (492 lines)

`createPayment(array $data): Payment:`
- Create payment record
- Update invoice paid amount
- Recalculate payment_status (unpaid/partial/paid)
- Create CashTransaction (deposit for received, withdrawal for paid)
- Fire `PaymentReceived` event

`cancelPayment($id):`
- Reverse invoice paid amount
- Reverse CashTransaction
- Fire `PaymentCancelled` event

### 7.24 CashService (394 lines)

`deposit(array $data): CashTransaction:` - wrapper for AccountingService::addDeposit

`withdraw(array $data): CashTransaction:` - wrapper for AccountingService::addWithdrawal

`transfer(array $data):` - between accounts (not fully implemented)

`getBalance(): float:` - cash balance

`getTransactions(array $filters):` - filterable

### 7.25 SettingsService (112 lines)

`getCompany(): ?Company:` - first Company record

`updateCompany(array $data):` - update company, handle logo upload

`deleteLogo():` - delete company logo file

`getSystemSettings(): array:` - key-value pairs from system_settings table

`updateSystemSetting(string $key, $value):` - upsert system_setting

`createUser(array $data): User:` - create with hashed password

`updateUser($id, array $data): User:` - optionally update password

`deleteUser($id):` - soft delete, prevent self-deletion

### 7.26 SlackService (243 lines)

`sendMessage(string $channel, string $message):` - send to Slack webhook

`sendLowStockAlert(Warehouse $warehouse, Product $product, float $quantity):` - formatted alert

---

## 8. Form Request Validation Rules

### 8.1 StoreProductRequest
```
name: required|string|max:255
code: nullable|string|max:100
sku: nullable|string|unique:products,sku
barcode: nullable|string|max:100
description: nullable|string
category_id: nullable|exists:categories,id
purchase_price: required|numeric|min:0
selling_price: required|numeric|min:0
min_stock: nullable|integer|min:0
unit_type: required|string
image: nullable|image|max:2048
is_active: boolean
product_type: required|in:raw,finished,semi_finished
base_units: nullable|array
base_units.*.unit_code: required|string
base_units.*.unit_name: required|string
base_units.*.base_purchase_price: required|numeric|min:0
base_units.*.base_selling_price: required|numeric|min:0
selling_units: nullable|array
selling_units.*.unit_code: required|string
selling_units.*.unit_name: required|string
selling_units.*.conversion_factor: required|numeric|min:0.01
selling_units.*.unit_selling_price: required|numeric|min:0
warehouses: nullable|array
warehouses.*.warehouse_id: required|exists:warehouses,id
warehouses.*.quantity: required|numeric|min:0
warehouses.*.min_stock: nullable|integer|min:0
```

### 8.2 UpdateProductRequest
Same as StoreProductRequest but sku unique check excludes current model.

### 8.3 CustomerRequest
```
name: required|string|max:255
phone: nullable|string|max:20
email: nullable|email|max:255
address: nullable|string
credit_limit: nullable|numeric|min:0
is_active: boolean
type: required|in:individual,company
notes: nullable|string
```

### 8.4 SupplierRequest
```
name: required|string|max:255
phone: nullable|string|max:20
email: nullable|email|max:255
address: nullable|string
is_active: boolean
notes: nullable|string
```

### 8.5 StoreWarehouseRequest
```
name: required|string|max:255
code: required|string|max:50|unique:warehouses,code
location: nullable|string
is_active: boolean
```

### 8.6 invoiceRequest (Sales Invoice)
```
customer_id: required|exists:customers,id
warehouse_id: required|exists:warehouses,id
invoice_date: required|date
due_date: nullable|date
discount_type: nullable|in:percentage,fixed
discount_value: nullable|numeric|min:0
tax_rate: nullable|numeric|min:0|max:100
shipping_cost: nullable|numeric|min:0
other_charges: nullable|numeric|min:0
notes: nullable|string
items: required|array|min:1
items.*.product_id: required|exists:products,id
items.*.selling_unit_id: nullable|exists:product_selling_units,id
items.*.quantity: required|numeric|min:0.01
items.*.price: required|numeric|min:0
items.*.tax_rate: nullable|numeric|min:0|max:100
```

### 8.7 PurchaseInvoiceRequest
```
supplier_id: required|exists:suppliers,id
warehouse_id: required|exists:warehouses,id
invoice_date: required|date
discount: nullable|numeric|min:0
tax: nullable|numeric|min:0
notes: nullable|string
items: required|array|min:1
items.*.product_id: required|exists:products,id
items.*.qty: required|numeric|min:0.01
items.*.price: required|numeric|min:0
```

### 8.8 StoreTransferRequest
```
from_warehouse_id: required|exists:warehouses,id|different:to_warehouse_id
to_warehouse_id: required|exists:warehouses,id
transfer_date: required|date
notes: nullable|string
items: required|array|min:1
items.*.product_id: required|exists:products,id
items.*.quantity_sent: required|numeric|min:0.01
```

### 8.9 StoreStockCountRequest
```
warehouse_id: required|exists:warehouses,id
count_type: required|in:full,partial
count_date: required|date
notes: nullable|string
product_ids: nullable|array (required if count_type=partial)
product_ids.*: exists:products,id
```

### 8.10 CountItemRequest
```
counted_quantity: required|numeric|min:0
notes: nullable|string
```

### 8.11 BulkPriceUpdateRequest
```
base_unit: required|string
category_id: nullable|integer
profit_type: required|in:percentage,fixed
profit_value: required|numeric|min:0
selected_products: required|json|max:5000_products
```

### 8.12 StoreManufacturingCostRequest
```
product_id: nullable|exists:products,id
cost_name: required|string|max:255
components: required|array
components.*.component_type: required|in:raw_material,hardware,other
components.*.component_name: required|string
components.*.thickness_cm: nullable|numeric|min:0
components.*.width_cm: nullable|numeric|min:0
components.*.length_cm: nullable|numeric|min:0
components.*.quantity: required|numeric|min:1
components.*.price_per_cubic_meter: required|numeric|min:0
waste_cost: nullable|numeric|min:0
labor_cost: nullable|numeric|min:0
nails_cost: nullable|numeric|min:0
tips_cost: nullable|numeric|min:0
transport_cost: nullable|numeric|min:0
fumigation_cost: nullable|numeric|min:0
profit_margin: nullable|numeric|min:0|max:100
notes: nullable|string
```

### 8.13 StoreManufacturingOrderRequest
```
product_id: nullable|exists:products,id
product_name: required|string
quantity_produced: required|numeric|min:1
warehouse_id: nullable|exists:warehouses,id
components: required|array
components.*.component_type: required|in:raw_material,hardware,other
components.*.component_name: required|string
components.*.thickness_cm: nullable|numeric
components.*.width_cm: nullable|numeric
components.*.length_cm: nullable|numeric
components.*.quantity: required|numeric|min:1
components.*.price_per_cubic_meter: required|numeric|min:0
waste_cost: nullable|numeric|min:0
labor_cost: nullable|numeric|min:0
nails_cost: nullable|numeric|min:0
tips_cost: nullable|numeric|min:0
transport_cost: nullable|numeric|min:0
fumigation_cost: nullable|numeric|min:0
profit_margin: nullable|numeric|min:0|max:100
notes: nullable|string
```

### 8.14 AccountingRequest
```
amount: required|numeric|min:0.01
description: nullable|string
category: nullable|string
transaction_date: required|date
reference: nullable|string
```

### 8.15 Accounting/ExpenseRequest
```
expense_category_id: nullable|exists:expense_categories,id
amount: required|numeric|min:0.01
description: required|string
expense_date: required|date
payment_method: required|in:cash,bank,card
reference: nullable|string
type: required|in:rent,salaries,utilities,maintenance,marketing,office_supplies,travel,insurance,other
```

### 8.16 Accounting/PaymentRequest
```
payable_type: required|string
payable_id: required|integer
amount: required|numeric|min:0.01
payment_method: required|in:cash,bank_transfer,check,card
payment_date: required|date
reference_number: nullable|string
notes: nullable|string
```

### 8.17 SalesReturnRequest
```
sales_invoice_id: required|exists:sales_invoices,id
items: required|array|min:1
items.*.product_id: required|exists:products,id
items.*.quantity: required|numeric|min:0.01
items.*.price: required|numeric|min:0
reason: nullable|string
notes: nullable|string
```

### 8.18 PurchaseReturnRequest
```
purchase_invoice_id: nullable|exists:purchase_invoices,id
supplier_id: required|exists:suppliers,id
items: required|array|min:1
items.*.product_id: required|exists:products,id
items.*.quantity: required|numeric|min:0.01
items.*.price: required|numeric|min:0
reason: nullable|string
notes: nullable|string
```

### 8.19 ReportingRequest
```
date_from: nullable|date
date_to: nullable|date|after_or_equal:date_from
warehouse_id: nullable|exists:warehouses,id
category_id: nullable|exists:categories,id
report_type: nullable|in:financial,inventory,profit_loss
```

### 8.20 SupplierPaymentRequest
```
supplier_id: required|exists:suppliers,id
amount: required|numeric|min:0.01
payment_method: required|in:cash,bank_transfer,check
payment_date: required|date
reference: nullable|string
notes: nullable|string
```

---

## 9. Filament Resources

### 9.1 ProductResource

**Model:** Product
**Navigation:** icon heroicon-o-cube, group 'المخزون', sort 1
**RecordTitleAttribute:** name

**Form:**
- name: TextInput required
- code: TextInput
- sku: TextInput unique
- barcode: TextInput
- description: Textarea
- category_id: Select (from Category model)
- purchase_price: TextInput numeric, default 0
- selling_price: TextInput numeric, default 0
- min_stock: TextInput integer, default 10
- unit_type: Select (weight/length/volume/quantity/area)
- product_type: Select (raw/finished/semi_finished)
- is_manufactured: Toggle
- is_active: Toggle default true
- image: FileUpload
- **Repeater** base_units: unit_code, unit_name, base_purchase_price, base_selling_price
- **Repeater** selling_units: unit_code, unit_name, conversion_factor, unit_purchase_price, unit_selling_price, is_base, auto_calculate_price, is_active
- **Repeater** warehouses: warehouse_id (Select), quantity, min_stock
- **Section** Pricing: base pricing, price history

**Table:**
- Columns: name, code, sku, category.name, purchase_price, selling_price, unit_type, is_active, stock_summary
- Filters: category, unit_type, is_active, low_stock
- Actions: Edit, Delete (admin only), View, Print Barcode
- Bulk Actions: Delete (admin only), Bulk Price Update
- Header Actions: Create (admin only), Export CSV

**Logic preserved:**
- `createProduct()` delegates to ProductService::createProduct()
- `updateProduct()` delegates to ProductService::updateProduct()
- `deleteProduct()` delegates to ProductService, checks admin
- Export uses existing CSV stream logic
- Barcode print uses existing dompdf logic

### 9.2 SalesInvoiceResource

**Model:** SalesInvoice
**Navigation:** icon heroicon-o-document-text, group 'المبيعات', sort 1

**Form:**
- customer_id: Select (active customers)
- warehouse_id: Select (active warehouses)
- invoice_date: DatePicker default today
- due_date: DatePicker
- **Repeater** items: product_id (searchable select), selling_unit_id (depends on product), quantity, price, tax_rate, tax_amount (computed), discount_amount, total (computed)
- discount_type: Select (percentage/fixed)
- discount_value: TextInput numeric
- tax_rate: TextInput numeric
- shipping_cost: TextInput numeric
- other_charges: TextInput numeric
- Computed: subtotal, total, remaining
- notes: Textarea

**Table:**
- Columns: invoice_number, invoice_date, customer.name, warehouse.name, total, paid, remaining, status, payment_status
- Filters: customer, warehouse, status, payment_status, date range, amount range
- Sort: invoice_date, total, paid, created_at
- Actions: View, Edit (not cancelled/paid), Cancel, Print, Record Payment
- Stats in header: total invoices, total amount, paid, pending, cancelled

**Logic preserved:**
- Invoice number auto-generated: SI-YYYYMMDD-NNNN
- Totals calculated: subtotal = sum(items), discount applied (percentage or fixed), tax, shipping, other -> total
- Status transitions: draft -> confirmed -> paid, or -> cancelled
- Stock decremented on create, restored on cancel
- Customer balance updated
- Events fired: SalesInvoiceCreated, SalesInvoiceCancelled

### 9.3 PurchaseInvoiceResource

**Model:** PurchaseInvoice
**Navigation:** group 'المشتريات'

**Form:**
- supplier_id: Select (active suppliers)
- warehouse_id: Select
- invoice_date: DatePicker
- **Repeater** items: product_id, qty, price, total (computed)
- discount: TextInput
- tax: TextInput
- notes: Textarea

**Table:**
- Columns: invoice_number, invoice_date, supplier.name, warehouse.name, subtotal, discount, tax, total, paid, status
- Actions: View, Edit, Delete, Print, Export, Export Single
- Invoice number: PI-YYYYMMDD-NNNN

**Logic preserved:**
- Stock incremented on create
- Supplier balance updated
- Same create/update/cancel pattern as sales

### 9.4 CustomerResource

**Model:** Customer
**Navigation:** group 'العملاء'

**Form:**
- name, code (auto CUST-NNNN), phone, email, address, balance, credit_limit, is_active, type (individual/company), notes

**Table:**
- Columns: code, name, phone, email, balance, is_active, type
- Actions: View, Edit, Delete, Statement, Export Statement
- Filters: search (name/code/phone), is_active, type

**Logic preserved:** CustomerService::create/update/delete, statement generation

### 9.5 SupplierResource

**Model:** Supplier
**Navigation:** group 'الموردين'

**Form:** name, code (auto SUP-NNNN), phone, email, address, balance, is_active, notes

**Table:** code, name, phone, email, balance, is_active

**Actions:** View, Edit, Delete, Statement, Export Statement, Toggle Status, Payments

**Logic preserved:** SupplierService, SupplierStatementService, SupplierBalanceService

### 9.6 WarehouseResource

**Model:** Warehouse
**Navigation:** group 'المخزون'

**Form:** name, code (unique), location, is_active

**RelationManager:** ProductsRelationManager (product_warehouse pivot with quantity, min_stock, reserved_quantity)

**Table:** code, name, location, is_active, total_products, total_value

**Actions:** View, Edit, Delete, Add Product, Low Stock Report, Movements

### 9.7 WarehouseTransferResource

**Model:** WarehouseTransfer
**Navigation:** group 'المخزون'

**Form:**
- from_warehouse_id: Select (active warehouses)
- to_warehouse_id: Select (different from source)
- transfer_date: DatePicker
- **Repeater** items: product_id, quantity_sent
- notes: Textarea

**Table:** transfer_number, from_warehouse, to_warehouse, transfer_date, status, total_items, total_quantity_sent, total_quantity_received

**Actions:** View, Reverse, Cancel

**Logic preserved:** TransferService::createTransfer (validates, moves stock, creates movements), reverseTransfer, cancelTransfer. ProcessLargeTransferJob for large transfers.

### 9.8 WarehouseInboundOrderResource

**Model:** WarehouseInboundOrder
**Form:** warehouse_id, order_date, Repeater items (product_id, quantity, unit_price, total), notes

**Logic preserved:** Auto-completes on create, updates ProductWarehouse quantities

### 9.9 WarehouseOutboundOrderResource

**Model:** WarehouseOutboundOrder
**Form:** warehouse_id, order_date, purpose (sale/transfer/return/damage/sample/other), Repeater items, notes

**Logic preserved:** Initial status pending, approve sets completed, cancel sets cancelled

### 9.10 StockCountResource

**Model:** StockCount
**Navigation:** group 'المخزون'

**Form:**
- warehouse_id: Select
- count_type: Select (full/partial)
- count_date: DatePicker
- product_ids: MultiSelect (when partial)
- notes: Textarea

**Table:** count_number, warehouse, count_type, status, count_date, progress

**Custom Page: CountPage**
- Shows items with system_quantity, counted_quantity (editable), difference
- Approve individual items, approve all
- Start count, complete count, cancel count

**Logic preserved:** StockCountService full lifecycle - create, start, count items, approve adjustments, complete (updates stock), cancel

### 9.11 ManufacturingCostResource

**Model:** ManufacturingCost
**Navigation:** group 'التصنيع'

**Form:**
- product_id: Select
- cost_name: TextInput
- **Repeater** components: component_type (raw_material/hardware/other), component_name, thickness_cm, width_cm, length_cm, quantity, price_per_cubic_meter, component_cost (computed)
- waste_cost, labor_cost, nails_cost, tips_cost, transport_cost, fumigation_cost
- profit_margin: TextInput numeric
- Computed: total_material_cost, total_additional_cost, total_cost, cost_per_unit, profit_amount, selling_price
- notes, status (draft/confirmed)

**Logic preserved:** ManufacturingCostService::createCost, calculateCosts (volume calculation), confirmCost

### 9.12 ManufacturingOrderResource

**Model:** ManufacturingOrder
**Navigation:** group 'التصنيع'

**Form:**
- product_id: Select
- product_name: TextInput
- quantity_produced: TextInput numeric
- warehouse_id: Select
- **Repeater** components (same as ManufacturingCost)
- Additional costs (same 6 fields)
- profit_margin
- Computed: cost_per_unit, total_cost, selling_price_per_unit, profit_amount
- notes

**Table:** order_number, product_name, quantity_produced, total_cost, status, created_at

**Actions:** Confirm, Complete (select warehouse + product), Cancel (with reason), Print

**Logic preserved:** ManufacturingOrderService full lifecycle - create, confirm, complete (creates/updates product, updates stock, creates movement), cancel

### 9.13 SalesReturnResource

**Model:** SalesReturn
**Navigation:** group 'المبيعات'

**Form:**
- sales_invoice_id: Select (confirmed/paid invoices only)
- return_date: DatePicker
- **Repeater** items: product_id, quantity (max: available to return), price, total
- reason, notes

**Logic preserved:** ReturnService::createSalesReturn - validates invoice, creates return, restores stock, updates customer balance, fires SalesReturnProcessed

### 9.14 PurchaseReturnResource

**Model:** PurchaseReturn
**Navigation:** group 'المشتريات'

**Form:**
- purchase_invoice_id: Select (optional)
- supplier_id: Select
- return_date: DatePicker
- **Repeater** items: product_id, quantity (checks available), price, total
- reason, notes

**Logic preserved:** PurchaseReturnService - validates available items, creates return, deducts stock, updates supplier balance

### 9.15 ExpenseResource

**Model:** Expense
**Navigation:** group 'المحاسبة'

**Form:**
- expense_category_id: Select
- amount, description, expense_date, payment_method (cash/bank/card), reference, type (rent/salaries/utilities/maintenance/marketing/office_supplies/travel/insurance/other)

**Logic preserved:** ExpenseService - creates expense + CashTransaction withdrawal

### 9.16 PaymentResource

**Model:** Payment
**Navigation:** group 'المحاسبة'

**Form:**
- payable_type: Select (SalesInvoice/PurchaseInvoice)
- payable_id: Select (filtered by type)
- amount, payment_method, payment_date, reference_number, notes

**Logic preserved:** PaymentService - creates payment, updates invoice paid status, creates CashTransaction, fires PaymentReceived event

### 9.17 SupplierPaymentResource

**Model:** SupplierPayment
**Navigation:** group 'الموردين'

**Form:** supplier_id, amount, payment_method, payment_date, reference, notes

**Logic preserved:** Creates directly on model, updates supplier balance

### 9.18 InventoryMovementResource

**Model:** InventoryMovement
**Navigation:** group 'المخزون'
**Read-only** (movements created by other services)

**Table:** movement_number, warehouse, product, movement_type, quantity, quantity_change, quantity_before, quantity_after, reference, movement_date, created_by

**Filters:** warehouse, product, movement_type, date range

### 9.19 UserResource

**Model:** User
**Navigation:** group 'الإعدادات', visible to admin only

**Form:** name, email, password (required on create, optional on edit), phone, role (admin/employee), is_active

**Table:** name, email, phone, role, is_active, created_at

**Actions:** Toggle Active (prevent self-deactivation), Delete (prevent self-deletion, soft delete)

### 9.20 RoleResource

**Model:** Role
**Navigation:** group 'الإعدادات', visible to admin only

**Form:** name, display_name, description, color
- Permissions: checkbox list grouped by module (10 modules, 49 permissions)

**Logic preserved:** System roles cannot be edited/deleted. Role-permission sync via pivot.

### 9.21 PermissionResource

**Model:** Permission
**Navigation:** group 'الإعدادات', visible to admin only
**Read-only** (permissions seeded, not created via UI)

**Table:** name, display_name, module, action, is_system

---

## 10. Filament Pages & Widgets

### 10.1 DashboardPage

**Widgets:**
1. **StatsOverviewWidget:** today_sales, today_purchases, month_sales, month_purchases, cash_balance, total_customers, total_suppliers, total_products
2. **RecentInvoicesWidget:** latest 10 sales invoices table
3. **LowStockAlertsWidget:** products below min_stock
4. **ChartWidget:** daily sales chart for last 30 days
5. **TopProductsWidget:** top 5 products by revenue this month
6. **TopCustomersWidget:** top 5 customers by purchase amount

**Data source:** ReportingService::dashboardSummary()

### 10.2 TreasuryPage (Custom Page)

**Under Accounting navigation group**

Displays:
- Cash balance card
- Bank balance card
- Today's transactions table
- Cash statistics (today deposits/withdrawals/net, month deposits/withdrawals/net)
- Categories list
- Deposit form (inline action)
- Withdrawal form (inline action)

**Data source:** AccountingService

### 10.3 FinancialReportPage (Custom Page)

**Under Reports navigation group**

- Date range filter
- Revenue breakdown
- COGS breakdown
- Gross profit
- Operating expenses by category
- Net profit
- Top products table
- Top customers table
- Export button (FinancialReportExport)

**Data source:** ReportingService::getFinancialReport()

### 10.4 InventoryReportPage (Custom Page)

- Warehouse filter
- Products with stock levels
- Total value calculation
- Low stock alerts
- Export button (InventoryReportExport)

**Data source:** ReportingService::getInventoryReport()

### 10.5 ProfitLossReportPage (Custom Page)

- Date range filter
- Revenue, COGS, Gross Profit, Expenses, Net Profit
- Export button (ProfitLossReportExport)

**Data source:** ReportingService::getProfitLossReport()

### 10.6 CustomerStatementPage (Custom Page)

Accessed from CustomerResource View action
- Date range filter, status filter
- Transactions table (invoices, payments, returns)
- Running balance
- Export button (CustomerStatementExport)

**Data source:** CustomerService::getCustomerStatement()

### 10.7 SupplierStatementPage (Custom Page)

Accessed from SupplierResource View action
- Date range filter
- Transactions table (invoices, payments, returns)
- Running balance
- Export button (SupplierStatementExport)

**Data source:** SupplierStatementService::generateStatement()

### 10.8 SettingsPage (Custom Page)

**Under Settings navigation group, admin only**

- Company Settings: name, phone, email, address, tax_number, commercial_register, logo upload
- System Settings: currency, date_format, tax rate, rows_per_page, boolean flags
- Logo delete button

**Data source:** SettingsService

### 10.9 BulkPriceUpdatePage (Custom Page)

**Under Products navigation group**

- Select base unit
- Select category (filtered by unit)
- Select profit type (percentage/fixed)
- Set profit value
- Select products (max 5000)
- Preview changes
- Apply changes

**Data source:** AdvancedPricingService / PriceUpdateService

### 10.10 PermissionsPage (Custom Page)

**Under Settings navigation group, admin only, requires users.permissions gate**

- User list with roles and permissions
- Edit user permissions/roles (checkbox lists)
- Role management (CRUD)
- Role permissions management
- Print report

**Data source:** PermissionsController logic (direct model queries)

---

## 11. Exports

All existing exports preserved exactly:

| Export | Model | Columns | Filters |
|--------|-------|---------|---------|
| CustomersExport | Customer | code, name, phone, email, balance, is_active, created_at | search, is_active, balance |
| CustomerStatementExport | Customer | date, type, reference, debit, credit, running_balance, notes | date_from, date_to, status |
| SuppliersExport | Supplier | code, name, phone, email, balance, is_active, created_at | search, is_active, balance |
| SupplierStatementExport | Supplier | date, type, reference, debit, credit, running_balance, notes | date_from, date_to, type |
| FinancialReportExport | SalesInvoice | date, invoice_number, customer, total, status, items | date_from, date_to |
| InventoryReportExport | ProductWarehouse | product, warehouse, quantity, min_stock, value | warehouse_id |
| ProfitLossReportExport | Mixed | revenue, cogs, expenses, profit | date_from, date_to |
| PurchaseInvoicesExport | PurchaseInvoice | invoice_number, date, supplier, warehouse, amounts, status | supplier, warehouse, status, date range |
| PurchaseInvoiceDetailsExport | PurchaseInvoiceItem | #, product, qty, price, total | specific invoice |

All exports use maatwebsite/excel with Arabic headers, purple/green styling, bold headers, borders, auto-size.

In Filament, these become Filament Export Actions using the same Export classes.

---

## 12. Jobs & Observers

### 12.1 ProcessLargeTransferJob

**Queue:** transfers
**Tries:** 3
**Timeout:** 600s

**Logic:**
1. Lock transfer for update
2. Validate status is 'draft'
3. Set status to 'processing'
4. Process items in chunks of 500:
   - Decrement source warehouse stock
   - Increment destination warehouse stock
   - Create inventory movements
   - Update progress in cache
5. Set status to 'received', set confirmed/received info
6. Update all items quantity_received = quantity_sent
7. Clear cache

**Failure handling:**
- Set status to 'failed', error_message, failed_at
- Log all errors
- Retry up to 3 times

**Filament:** This job is dispatched from the TransferService, which is called by the Filament Resource. No changes needed.

### 12.2 ProductBaseUnitObserver

On `updated`:
1. Update products.purchase_price and products.selling_price
2. If auto_update_selling_units: update base selling unit, then all auto-calculated sub-units using conversion_factor

**Filament:** Observer runs on model events. Filament triggers the same Eloquent events. No changes needed.

### 12.3 Custom Console Commands

- `db:optimize` - OPTIMIZE TABLE on all tables
- `products:sync-base-pricing` - Create ProductBasePricing for active products missing it

**Filament:** These remain as artisan commands. Can optionally be added as Filament Actions on settings page.

---

## 13. Notifications

All 10 notifications preserved:

| Notification | Trigger | Recipients | Channel |
|-------------|---------|------------|---------|
| NewSalesInvoiceNotification | SalesInvoiceCreated | Admins | database |
| NewPurchaseInvoiceNotification | PurchaseInvoiceCreated | Admins | database |
| InvoiceCancelledNotification | Invoice cancelled | Admins | database |
| PaymentReceivedNotification | PaymentReceived | Related user | database |
| SalesReturnNotification | SalesReturnProcessed | Admins | database |
| PurchaseReturnNotification | PurchaseReturnProcessed | Admins | database |
| LowStockNotification | StockLow | Admins | database + Slack |
| TransferInitiatedNotification | TransferInitiated | Admins | database |
| TransferCompletedNotification | TransferCompleted | Admins | database |
| SystemNotification | Various | Specific users | database |

**Filament:** Notifications continue to work via Laravel's notification system. Filament has built-in database notification support.

---

## 14. Configuration

### 14.1 App Config
- Timezone: UTC
- Locale: en (but UI is Arabic - RTL)
- Cipher: AES-256-CBC

### 14.2 Database
- MySQL, utf8mb4, strict mode
- Redis available (predis)

### 14.3 Auth
- Session-based (web guard)
- Sanctum installed (API only)
- Password reset: 60 min expiry

### 14.4 Mail
- SMTP (Mailpit in dev)

### 14.5 Queue
- Default: sync (synchronous)
- Redis available for queue driver

### 14.6 Filament-Specific Config Needed

```php
// config/filament.php or in AdminPanelProvider
->defaultLocale('ar')
->RTL()
->authGuard('web')
->brandName('Magzani ERP')
->discoverResources()
->discoverPages()
->discoverWidgets()
```

---

## 15. Cache Keys

The application uses these cache key patterns that must be preserved:

- `warehouse_products_stock_{warehouseId}`
- `warehouse_stats_{warehouseId}`
- `transfer_progress_{transferId}`
- `product_prices` (cache tag)
- `current_price_{productId}_{baseUnit}`
- `customer_*`
- `supplier_*`
- `bulk_price_update_page_data`

---

## 16. Implementation Order

### Phase 1: Foundation (Files 1-5)
1. Install Filament (`composer require filament/filament:"^3.2"`)
2. Create `AdminPanelProvider` with auth, RTL, Arabic locale
3. Create custom Filament middleware (RoleMiddleware, AdminOnly equivalents)
4. Set up navigation groups in Arabic
5. Create base Resource classes with shared config

### Phase 2: Core Resources (Files 6-15)
6. CategoryResource (simple CRUD)
7. WarehouseResource + ProductsRelationManager
8. ProductResource (most complex form with repeaters)
9. CustomerResource
10. SupplierResource + SupplierPaymentResource
11. UserResource
12. RoleResource + PermissionResource
13. InventoryMovementResource (read-only)

### Phase 3: Invoice & Returns (Files 16-22)
14. SalesInvoiceResource (items repeater, calculations, status management)
15. PurchaseInvoiceResource
16. SalesReturnResource
17. PurchaseReturnResource

### Phase 4: Warehouse Operations (Files 23-27)
18. WarehouseTransferResource (stock movement, reversal, cancellation)
19. WarehouseInboundOrderResource
20. WarehouseOutboundOrderResource
21. StockCountResource + Custom CountPage

### Phase 5: Manufacturing (Files 28-30)
22. ManufacturingCostResource (BOM calculator)
23. ManufacturingOrderResource (full lifecycle)

### Phase 6: Accounting & Reports (Files 31-38)
24. ExpenseResource
25. PaymentResource
26. TreasuryPage (custom page)
27. FinancialReportPage
28. InventoryReportPage
29. ProfitLossReportPage
30. CustomerStatementPage
31. SupplierStatementPage

### Phase 7: Dashboard & Settings (Files 39-43)
32. Dashboard with widgets
33. SettingsPage
34. BulkPriceUpdatePage
35. PermissionsPage
36. Export actions on all resources

### Phase 8: Cleanup (Files 44-47)
37. Remove old Blade views and controllers
38. Remove web routes (replaced by Filament)
39. Keep: Services, Models, Events, Listeners, Observers, Jobs, Migrations, Policies
40. Test every workflow end-to-end

---

## File Mapping: What Stays, What Goes, What's New

### KEEP (unchanged):
- `app/Models/*` (all 46 models)
- `app/Services/*` (all 26 services)
- `app/Events/*` (all 16 events)
- `app/Listeners/*` (all 20 listeners)
- `app/Notifications/*` (all 10 notifications)
- `app/Policies/*` (both policies)
- `app/Observers/*` (ProductBaseUnitObserver)
- `app/Jobs/*` (ProcessLargeTransferJob)
- `app/Exports/*` (all 9 exports)
- `app/Traits/*` (all 5 traits)
- `app/Exceptions/Handler.php`
- `app/Console/Commands/*` (both commands)
- `database/*` (all migrations, seeders, factories)
- `config/*` (all config files)
- `lang/*` (Arabic translations)

### REMOVE (replaced by Filament):
- `app/Http/Controllers/*` (all 28 controllers)
- `app/Http/Middleware/RoleMiddleware.php` (replaced by Filament middleware)
- `app/Http/Middleware/AdminOnly.php` (replaced by Filament auth)
- `app/Http/Kernel.php` (Laravel 10 specific, Filament handles middleware)
- `app/Http/Requests/*` (all 31 form requests - validation moves to Filament forms)
- `resources/views/*` (all 86 Blade templates)
- `routes/web.php` (replaced by Filament resource routes)
- `resources/js/*` (Vue components replaced by Filament)

### CREATE (new):
- `app/Providers/Filament/AdminPanelProvider.php`
- `app/Filament/Resources/ProductResource.php` (+ Pages)
- `app/Filament/Resources/SalesInvoiceResource.php` (+ Pages)
- `app/Filament/Resources/PurchaseInvoiceResource.php` (+ Pages)
- `app/Filament/Resources/CustomerResource.php` (+ Pages)
- `app/Filament/Resources/SupplierResource.php` (+ Pages)
- `app/Filament/Resources/WarehouseResource.php` (+ Pages + RelationManagers)
- `app/Filament/Resources/WarehouseTransferResource.php` (+ Pages)
- `app/Filament/Resources/WarehouseInboundOrderResource.php`
- `app/Filament/Resources/WarehouseOutboundOrderResource.php`
- `app/Filament/Resources/StockCountResource.php` (+ Custom Pages)
- `app/Filament/Resources/SalesReturnResource.php`
- `app/Filament/Resources/PurchaseReturnResource.php`
- `app/Filament/Resources/ManufacturingCostResource.php`
- `app/Filament/Resources/ManufacturingOrderResource.php`
- `app/Filament/Resources/ExpenseResource.php`
- `app/Filament/Resources/PaymentResource.php`
- `app/Filament/Resources/SupplierPaymentResource.php`
- `app/Filament/Resources/InventoryMovementResource.php`
- `app/Filament/Resources/UserResource.php`
- `app/Filament/Resources/RoleResource.php`
- `app/Filament/Resources/PermissionResource.php`
- `app/Filament/Resources/CategoryResource.php`
- `app/Filament/Pages/Dashboard.php`
- `app/Filament/Pages/TreasuryPage.php`
- `app/Filament/Pages/FinancialReportPage.php`
- `app/Filament/Pages/InventoryReportPage.php`
- `app/Filament/Pages/ProfitLossReportPage.php`
- `app/Filament/Pages/SettingsPage.php`
- `app/Filament/Pages/BulkPriceUpdatePage.php`
- `app/Filament/Pages/PermissionsPage.php`
- `app/Filament/Pages/CustomerStatementPage.php`
- `app/Filament/Pages/SupplierStatementPage.php`
- `app/Filament/Widgets/*` (dashboard widgets)
- `app/Filament/Middleware/CheckActive.php` (is_active check)
- `app/Filament/Middleware/CheckRole.php` (role/permission middleware)

---

## Critical Business Rules Summary

### Invoice Number Generation
- Sales: `SI-YYYYMMDD-NNNN` (auto-increment per day)
- Purchase: `PI-YYYYMMDD-NNNN`
- Sales Return: `SRET-YYYYMMDD-NNNN`
- Purchase Return: `PRET-YYYYMMDD-NNNN`
- Transfer: `TR-YYYYMMDD-NNNN`
- Stock Count: `SC-YYYYMMDD-NNNN`
- Manufacturing Order: `MO-YYYY-NNNN`
- Movement: `MV-YYYYMMDD-HHMMSS-{productId}`
- Inbound Order: auto-generated
- Outbound Order: auto-generated

### Stock Movement Rules
- Sales Invoice: DECREMENT product_warehouse
- Purchase Invoice: INCREMENT product_warehouse
- Sales Return: INCREMENT product_warehouse
- Purchase Return: DECREMENT product_warehouse
- Transfer Out: DECREMENT source warehouse
- Transfer In: INCREMENT destination warehouse
- Manufacturing Complete: INCREMENT warehouse (creates product if not exists)
- Inbound Order: INCREMENT warehouse
- Outbound Order: pending (no stock change until approved)
- Stock Count Adjustment: INCREMENT or DECREMENT to match counted quantity

### Balance Calculation
- Customer balance += sales_invoice.total
- Customer balance -= sales_return.total_return_amount
- Customer balance -= payment.amount (received from customer)
- Supplier balance += purchase_invoice.total
- Supplier balance += purchase_return.total_return_amount
- Supplier balance -= supplier_payment.amount

### Cash Balance
- Cash balance = SUM(deposits) - SUM(withdrawals)
- Payment received -> CashTransaction deposit
- Payment made (supplier) -> CashTransaction withdrawal
- Expense -> CashTransaction withdrawal
- Insufficient balance check on withdrawals

### Manufacturing Cost Formula
```
component_volume_cm3 = quantity * thickness_cm * width_cm * length_cm
component_cost = (component_volume_cm3 / 1000000) * price_per_cubic_meter
total_material_cost = SUM(component_costs)
total_additional_cost = waste + labor + nails + tips + transport + fumigation
cost_per_unit = total_material_cost + total_additional_cost
profit_per_unit = cost_per_unit * (profit_margin / 100)
selling_price_per_unit = cost_per_unit + profit_per_unit
total_cost = cost_per_unit * quantity_produced
total_profit = profit_per_unit * quantity_produced
```

### Status Transition Rules

**Sales Invoice:** draft -> confirmed -> paid, any non-paid -> cancelled
**Purchase Invoice:** pending -> paid, any -> cancelled
**Warehouse Transfer:** draft -> sent -> received, draft -> processing -> received, any non-received -> cancelled, received -> reversed
**Stock Count:** draft -> counting -> completed -> approved, draft/counting -> cancelled
**Manufacturing Cost:** draft -> confirmed
**Manufacturing Order:** draft -> confirmed -> completed, draft/confirmed -> cancelled
**Outbound Order:** pending -> completed, pending -> cancelled

### Discount Logic
- Type 'percentage': discount_amount = subtotal * (discount_value / 100)
- Type 'fixed': discount_amount = discount_value

### Tax Logic
- tax_amount = (subtotal - discount_amount) * (tax_rate / 100)
- Applied per-item or per-invoice depending on invoice type

### Unit Pricing (ProductStockManagement trait)
- Base unit has purchase_price and selling_price
- Selling units have conversion_factor from base unit
- unit_purchase_price = base_purchase_price * conversion_factor
- unit_selling_price = base_selling_price * conversion_factor
- Auto-calculate can be enabled/disabled per selling unit

### Currency
- All amounts in decimal(15,2) - no currency conversion
- Display with "جنيه" (Egyptian Pound) suffix on print views
