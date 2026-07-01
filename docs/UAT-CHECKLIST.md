# UAT checklist — Magzani (pre-production)

Use this on **staging** with a copy of production-like data. Record pass/fail and notes per row.

## Environment

- [ ] `APP_ENV=staging`, `APP_DEBUG=false`, HTTPS, valid mail/session config
- [ ] Migrations up to date; seed or import users/warehouses as needed
- [ ] Smoke: `php artisan test` passes in CI/staging

## Authentication & roles

- [ ] Guest: `/` redirects to login; `/home` redirects (if still routed) as expected
- [ ] Valid login (active user) → dashboard or intended URL
- [ ] Wrong password / unknown email → validation message (no leak of which failed)
- [ ] Inactive user cannot log in
- [ ] **Admin**: dashboard, products, warehouses, manufacturing orders, sales **create/edit**, settings (as designed)
- [ ] **Employee**: blocked from admin-only routes (e.g. sales create returns 403 JSON if hit directly); can use allowed modules (e.g. sales index per your policy)

## Products & catalog

- [ ] Products index: stats match filtered set (not only current page); category shows name or fallback
- [ ] Quick search returns manufactured / legacy-status products where policy allows
- [ ] After **manufacturing order complete**: product appears in catalog and in sales product picker with correct warehouse pivot

## Manufacturing orders

- [ ] Create → confirm → complete flow; stock/movements consistent
- [ ] Completed order with `product_id`: link from order show to **new sales invoice** prefill works
- [ ] Cancel/reopen behaviour matches business rules

## Sales invoices

- [ ] Create invoice: customer, warehouse, lines, conversion factor if used
- [ ] Prefill query params (`customer_id`, `warehouse_id`, `product_id`, `quantity`) when linked from manufacturing
- [ ] Posting updates stock; print/PDF if applicable

## Sales returns

- [ ] Create return: select **invoice line** (`sales_invoice_item_id`), not product alone
- [ ] Stock restored by **base quantity** (quantity × conversion)
- [ ] Destroy/cancel reverses stock correctly

## Warehouses & inventory

- [ ] Warehouse show: product stock section matches movements
- [ ] Transfers / low stock (if used) still coherent after above flows

## Regression quick pass

- [ ] Purchase invoices (if in scope) unchanged
- [ ] Legacy `/manufacturing` cost confirm still creates/updates manufactured product when expected

---

**Staging note:** Run this checklist after each deploy; keep one admin and one employee test account documented only in your secure runbook (not in the repo).
