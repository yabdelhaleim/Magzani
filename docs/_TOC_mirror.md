# Magzani ERP — Quick TOC

Project: C:\MAGZANIV6\Magzani  |  Generated: 2026-07-04

**Open the full DOCX for tables, code, diagrams. This file is a quick table of contents.**


## Table of Contents

## Part 1: Project Overview
- **1.1 What is Magzani?**
- **1.2 Business Domain**
- **1.3 Target Users**
- **1.4 Project Status**

## Part 2: Application Architecture
- **2.1 Multi-Tenancy Architecture**
- **2.2 Landlord vs Tenant Separation**
- **2.3 Request Lifecycle**
- **2.4 Tenancy Resolution Flow**
- **2.5 Tenant Provisioning Flow**

## Part 3: Tech Stack
- **3.1 Backend Stack**
- **3.2 Frontend Stack**
- **3.3 Dev Dependencies**
- **3.4 Database Configuration**

## Part 4: Complete File Tree
- **4.1 Root Directory**
- **4.2 app/ Directory Structure**
- **4.3 database/ Directory Structure**
- **4.4 resources/views/ Directory**
- **4.5 routes/ Directory**
- **4.6 config/ Directory**
- **4.7 Lang / Tests / Storage / Public**

## Part 5: Database Schema
- **5.1 Central (Landlord) Database Tables**
  - 5.1.1 tenants
  - 5.1.2 domains
  - 5.1.3 plans
  - 5.1.4 plan_features
- **5.2 Tenant Database Schema (per-tenant DB)**
  - 5.2.1 Auth & RBAC (5 tables)
  - 5.2.2 Catalog - Products & Pricing (~12 tables)
  - 5.2.3 Warehouses & Transfers (6 tables)
  - 5.2.4 Sales (4 tables)
  - 5.2.5 Purchases (4 tables)
  - 5.2.6 Partners (4 tables)
  - 5.2.7 Payments (3 tables)
  - 5.2.8 Stock Operations (3 tables)
  - 5.2.9 Inventory Movements (1 table)
  - 5.2.10 Cash & Expenses (3 tables)
  - 5.2.11 Manufacturing (5 tables)
  - 5.2.12 Wood Stock & Dispensing (2 tables)
  - 5.2.13 Accounting (~14 tables)
  - 5.2.14 Misc Tables
- **5.3 Key Model Relationships Summary**

## Part 6: Modules (Deep Dive)
- **Module 1: Authentication & RBAC**
  - Files
  - Key Fields / Models
  - Routes
  - Business Notes
- **Module 2: Multi-Tenancy / Landlord (Kayyan SaaS Admin)**
  - Files
  - Key Fields / Models
  - Routes
  - Business Notes
- **Module 3: Products & Pricing**
  - Files
  - Key Fields / Models
  - Routes
  - Business Notes
- **Module 4: Customers**
  - Files
  - Key Fields / Models
  - Routes
  - Business Notes
- **Module 5: Suppliers**
  - Files
  - Key Fields / Models
  - Routes
  - Business Notes
- **Module 6: Sales Invoices & Returns**
  - Files
  - Key Fields / Models
  - Routes
  - Business Notes
- **Module 7: Purchase Invoices & Returns**
  - Files
  - Key Fields / Models
  - Routes
  - Business Notes
- **Module 8: Warehouses & Transfers**
  - Files
  - Key Fields / Models
  - Routes
  - Business Notes
- **Module 9: Point of Sale & Shifts**
  - Files
  - Key Fields / Models
  - Routes
  - Business Notes
- **Module 10: Inventory, Stock Count & Movements**
  - Files
  - Key Fields / Models
  - Routes
  - Business Notes
- **Module 11: Manufacturing Orders & BOM**
  - Files
  - Key Fields / Models
  - Routes
  - Business Notes
- **Module 12: Accounting (Chart of Accounts, Journals, Reports)**
  - Files
  - Key Fields / Models
  - Routes
  - Business Notes
- **Module 13: Reporting & Dashboard**
  - Files
  - Routes
  - Business Notes
- **Module 14: Settings**
  - Files
  - Key Fields / Models
  - Routes
  - Business Notes
- **Module 15: Activity Logs & Notifications**
  - Files
  - Routes
  - Business Notes

## Part 7: Complete Routes Reference
- **7.1 Landlord Routes (routes/web.php)**
- **7.2 Tenant Routes (routes/tenant.php — 619 lines)**
  - 7.2.1 Auth & Public
  - 7.2.2 POS & Shifts (feature:pos)
  - 7.2.3 Sales Invoices (feature:pos, admin-only writes)
  - 7.2.4 Manufacturing (feature:manufacturing + admin.only)
  - 7.2.5 Warehouse Transfers (feature:multi_warehouse)
  - 7.2.6 Accounting (Quick) & Reports (feature:accounting, admin)
  - 7.2.7 Warehouses (feature:warehouses + role)
  - 7.2.8 Warehouse Orders (inbound + outbound)
  - 7.2.9 Stock Counts
  - 7.2.10 Inventory Movements
  - 7.2.11 Products (auth)
  - 7.2.12 Categories (auth)
  - 7.2.13 Purchases (feature:purchase + auth)
  - 7.2.14 Sales Returns (feature:pos + auth)
  - 7.2.15 Customers & Suppliers (auth)
  - 7.2.16 Settings (auth + admin)
  - 7.2.17 Users & Permissions (auth + admin)
  - 7.2.18 Advanced Accounting (feature:accounting_advanced + admin)
- **7.3 API Routes (routes/api.php)**

## Part 8: Business Logic Deep-Dive
- **8.1 Tenancy Internals**
  - 8.1.1 Tenant Model Deep-Dive
  - 8.1.2 TenancyServiceProvider
  - 8.1.3 Feature Middleware
  - 8.1.4 Tenant Provisioning (Recap)
- **8.2 POS Shift Lifecycle**
  - 8.2.1 Status State Machine
  - 8.2.2 Open Shift Flow
  - 8.2.3 During Sale (Livewire)
  - 8.2.4 Close Shift Flow
  - 8.2.5 X-Report & Z-Report
- **8.3 Accounting Posting Flow**
  - 8.3.1 PostingService (43 KB) - The Auto-Posting Engine
  - 8.3.2 Posting Failure Handling
  - 8.3.3 Year-End Closing Wizard
- **8.4 Role-Based Access Control (RBAC)**
  - 8.4.1 User Model Key Methods
  - 8.4.2 Gate::before in AuthServiceProvider
  - 8.4.3 Permission Seeding (22 KB matrix)
- **8.5 Subscriptions & Plans**
  - 8.5.1 Default Plans (PlanSeeder)
  - 8.5.2 Plan Resolution Flow
  - 8.5.3 Plan Limits
  - 8.5.4 Subscription Status
  - 8.5.5 Plan Upgrade UI
- **8.6 Reports & Exports**
  - 8.6.1 Excel Exports (Maatwebsite)
  - 8.6.2 PDF Generation (DomPDF)
  - 8.6.3 Reporting Service Hierarchy
- **8.7 AppServiceProvider (Cross-Cutting)**
- **8.8 Custom Middleware Summary**

## Part 9: Recent Changes & Known Issues
- **9.1 Recent Git History (git log --oneline -15)**
- **9.2 Recent File Modifications (last 48h)**
- **9.3 Known Issues (from ERROR_FIXES_TODO.md, SYSTEM_AUDIT_REPORT.md)**
  - 9.3.1 Critical
  - 9.3.2 Bugs Already Fixed (mention for awareness)
  - 9.3.3 Module Stability (per COMPLETION_SUMMARY_AR.md)
  - 9.3.4 Operational Caveats

## Part 10: Quick Reference Card
- **10.1 Default Credentials (per Plan Provisioning)**
- **10.2 Important URLs (Local Dev)**
- **10.3 Common Artisan Commands**
- **10.4 Default Plans & Feature Map**
- **10.5 Key Files To Remember**
- **10.6 Troubleshooting Recipe**
- **10.7 Production Deployment Checklist**
