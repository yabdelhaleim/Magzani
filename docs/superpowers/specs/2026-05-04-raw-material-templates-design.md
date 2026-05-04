# Raw Materials Templates (انشاء الخامات)

## Overview

A standalone template system for managing reusable raw materials recipes. Each template defines a set of wood components (type, dimensions, quantity, price) that can be loaded into manufacturing orders, avoiding repetitive data entry.

## Data Model

### `raw_material_templates`

| Column | Type | Notes |
|---|---|---|
| `id` | bigint PK | auto-increment |
| `name` | string(255) | template name, e.g. "فرش بالة 110×120" |
| `notes` | text | nullable |
| `created_by` | foreignId | nullable, users |
| `updated_by` | foreignId | nullable, users |
| `timestamps` | | |
| `softDeletes` | | |

### `raw_material_template_items`

| Column | Type | Notes |
|---|---|---|
| `id` | bigint PK | auto-increment |
| `template_id` | foreignId | constrained to raw_material_templates, cascade delete |
| `component_type` | enum | فرش, روباط, شاسية, دكم |
| `thickness_cm` | decimal(10,4) | thickness in cm |
| `width_cm` | decimal(10,4) | width in cm |
| `length_cm` | decimal(10,4) | length in meters (matches UI label) |
| `quantity` | decimal(10,4) | number of pieces |
| `price_per_meter` | decimal(10,4) | price per meter |
| `total_cost` | decimal(12,4) | calculated: length × quantity × price_per_meter |
| `sort_order` | unsignedInt | default 0 |
| `timestamps` | | |

### Models

- `RawMaterialTemplate` — HasMany `RawMaterialTemplateItem`, belongsTo creator/updater (User)
- `RawMaterialTemplateItem` — belongsTo `RawMaterialTemplate`

### Cost Calculation

Per item: `total_cost = length_cm * quantity * price_per_meter`

This matches the existing JS formula on the order create page (`length * quantity * price`).

## Routes

All under the existing `/manufacturing-orders` prefix with `auth` and `admin.only` middleware:

| Method | URI | Name | Purpose |
|---|---|---|---|
| GET | `/manufacturing-orders/raw-materials` | `manufacturing-orders.raw-materials.index` | List templates |
| GET | `/manufacturing-orders/raw-materials/create` | `manufacturing-orders.raw-materials.create` | Create form |
| POST | `/manufacturing-orders/raw-materials` | `manufacturing-orders.raw-materials.store` | Store template |
| GET | `/manufacturing-orders/raw-materials/{id}/edit` | `manufacturing-orders.raw-materials.edit` | Edit form |
| PUT | `/manufacturing-orders/raw-materials/{id}` | `manufacturing-orders.raw-materials.update` | Update template |
| DELETE | `/manufacturing-orders/raw-materials/{id}` | `manufacturing-orders.raw-materials.destroy` | Delete template |
| POST | `/manufacturing-orders/raw-materials/{id}/load` | `manufacturing-orders.raw-materials.load` | AJAX: return template items as JSON |

## Pages

### Index (`raw-materials/index.blade.php`)
- Lists all templates in a responsive table (same style as manufacturing-orders index)
- Columns: الاسم, عدد المكونات, إجمالي التكلفة, تاريخ الإنشاء, إجراءات (عرض/تعديل/حذف)
- "إنشاء قالب جديد" button at top

### Create (`raw-materials/create.blade.php`)
- Template name input
- Dynamic components table with columns: النوع, السمك (سم), العرض (سم), الطول (م), العدد, سعر المتر, التكلفة, إجراء
- "+" button to add rows, delete button per row
- JS auto-calculates cost per row: `length × quantity × price_per_meter`
- Save and Cancel buttons
- Same visual style (`.mfg-card`, `.mfg-table`, `.btn` classes) as existing manufacturing order pages

### Edit (`raw-materials/edit.blade.php`)
- Same as create, pre-populated with existing data

### Show (`raw-materials/show.blade.php`)
- Template name and notes
- Read-only components table with the same 7 columns
- Important note: "المكونات أدناه تمثل الخامات اللازمة لـ بالة واحدة فقط"
- Total cost summary

## Load Template into Order

On the existing manufacturing order create page (`/manufacturing-orders/create`):
- Add a "تحميل من قالب خامات" button above the wood components table
- Clicking it fetches available templates via AJAX and shows a dropdown
- Selecting a template calls the `/load` endpoint and populates the components table
- The loaded items replace any existing rows in the components table
- User can still manually add/remove/modify rows after loading

## Sidebar

Add to the existing "التصنيع" dropdown submenu in `layouts/app.blade.php`:
- Add a divider line
- Add "انشاء الخامات" link pointing to `manufacturing-orders.raw-materials.index`
- Use `fas fa-boxes-stacked` icon
- Include the new route names in the Alpine.js `open` condition and `active` class

## Files

### New
- `database/migrations/xxxx_create_raw_material_templates_table.php`
- `database/migrations/xxxx_create_raw_material_template_items_table.php`
- `app/Models/RawMaterialTemplate.php`
- `app/Models/RawMaterialTemplateItem.php`
- `app/Http/Controllers/RawMaterialTemplateController.php`
- `resources/views/manufacturing-orders/raw-materials/index.blade.php`
- `resources/views/manufacturing-orders/raw-materials/create.blade.php`
- `resources/views/manufacturing-orders/raw-materials/edit.blade.php`
- `resources/views/manufacturing-orders/raw-materials/show.blade.php`

### Modified
- `routes/web.php` — add CRUD + load routes
- `resources/views/layouts/app.blade.php` — sidebar menu item
- `resources/views/manufacturing-orders/create.blade.php` — load template button + AJAX
