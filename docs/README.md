# Magzani ERP — Documentation Hub

> **Source-of-truth reference for the Magzani codebase.**
> Always consult this folder before exploring files, before answering user questions, or before implementing features.

## Recent Engineering Highlights (Aug 2026)

The following changes were applied across the plans & feature-gating stack
(commit `9cb8bda`). When reading older notes about feature flags, treat them
as superseded by this list.

### Plans & feature activation

- `Tenant::hasFeature()` now resolves both legacy aliases (`sales`, `purchases`,
  `warehouses`, `warehouse`, `reports`, `advanced_accounting`) and canonical
  keys (`pos`, `purchase`, `multi_warehouse`, `accounting_advanced`,
  `reports_advanced`) through `Tenant::FEATURE_ALIASES` and
  `Tenant::resolveFeatureKey()`. The canonical `FEATURE_KEYS` set is the only
  truth persisted in the database.
- `Plan::hasFeature()` runs the same alias resolution so the public pricing
  page (and any other consumer) keeps working with legacy keys.
- `Tenant::customFeatureKeys()` is the single source for a tenant's selected
  custom features; both `hasFeature()` and `getPlanAttribute()` use it.
- `getPlanAttribute()` always reads from the central connection
  (`config('tenancy.database.central_connection')`) and synthesizes
  `PlanFeature` objects from `data.custom_features` for `plan_id=custom`
  tenants. The central `custom` Plan row is now a placeholder (marked
  `is_active=false` in `PlanSeeder`) and is never used as the runtime source.

### Middleware & caching

- `CheckPlanFeature` (`feature:*` middleware) reads the tenant's own
  `custom_features` for `plan_id=custom` rather than the shared central
  `custom` plan, and normalizes the requested feature before the cache lookup.
- Cache key is now `tenant_{id}_plan_v2`; `Tenant::invalidateFeatureCache()`
  clears it (and the legacy `*_plan` / `*_features` keys) on any tenant data
  change.
- `TenantObserver` invalidates the cache whenever the tenant's `data` JSON is
  dirty (not just on `plan_id` change), so toggling custom features takes
  effect on the next request.
- New `App\Observers\PlanObserver` invalidates tenant caches for any tenant
  subscribed to a plan whose `Plan` row is updated, deleted, or whose
  `slug` is changed, and also for any `plan_features` row that is created,
  updated, or deleted. This means the cache stays fresh even when a script
  or seed mutates plan features outside the dashboard.

### Super-admin dashboard

- Routes in `routes/web.php` under `/super-admin/*` are now wrapped with
  `auth` and the new `super.admin` middleware (`SuperAdminOnly`), which only
  allows the platform operator account (role `super_admin`).
- The `central` `custom` Plan row is now `is_active=false`, so it is hidden
  from the public super-admin plan lists while still being usable as the
  internal placeholder for per-tenant custom plans.
- Plan creation/edit syncs the `plan_features` pivot table
  (`SuperAdminController::syncPlanFeatures()`) and invalidates tenant
  caches (`invalidatePlanTenantCaches()`) for both the old and new slug.
- Custom-features and standard-features forms submit canonical keys
  (`pos`, `purchase`, `multi_warehouse`, `manufacturing`, `accounting`,
  `accounting_advanced`, `reports_advanced`, `stock_count`) and use
  `Tenant::normalizeFeatureKeys()` to deduplicate / validate before save.
- Duplicate "Sales" and "POS" checkboxes were merged in
  `landlord/tenants/create.blade.php` and `landlord/tenants/edit.blade.php`.

### Testing

- `tests/Feature/SubscriptionFeatureTest.php`:
  - `test_tenant_without_manufacturing_feature_is_forbidden` now asserts
    the redirect to `plan.upgrade` (the middleware's actual behavior).
  - `test_custom_tenant_uses_its_selected_features_and_aliases` covers
    custom-plan activation, alias resolution (`sales`→`pos`,
    `warehouses`→`multi_warehouse`), and that an unselected feature still
    blocks access.
  - `test_super_admin_plan_update_syncs_feature_rows` covers plan
    feature synchronization through the dashboard.
- `tests/Feature/Gap4bSidebarRouteTest.php` continues to pass.
- `tests/Feature/AccountingFeatureTest::test_accounting_system_workflow`
  has a pre-existing failure (`fiscal_period_id` is null when reversing
  journal entries); this is unrelated to the feature-gating work and
  should be tracked separately.


## Main Document

📄 **`Magzani_ERP_Full_Documentation.docx`** — comprehensive technical reference, ~88 KB, 191 headings, 76 tables, 10 Parts.

### How to use

1. Open the DOCX in Microsoft Word (or LibreOffice).
2. On first open the TOC will show "Right-click and Update Field" — do that to populate page numbers.
3. Use the TOC / sidebar navigation to jump directly to any section.

### Contents (the 10 Parts)

| Part | Title | Use this when |
|------|-------|---------------|
| 1 | Project Overview | Need a 30-second summary of what Magzani is |
| 2 | Application Architecture | Need to understand multi-tenancy / request lifecycle |
| 3 | Tech Stack | Need to look up package versions or dependencies |
| 4 | Complete File Tree | Need to locate a file or folder |
| 5 | Database Schema | Need column names, table list, or FK relationships |
| 6 | Modules (Deep Dive) | Need module-by-module behavior, files, fields, routes |
| 7 | Complete Routes Reference | Need to know all URL endpoints + names + middleware |
| 8 | Business Logic Deep-Dive | Need POS shift / accounting posting / RBAC / Plans flow |
| 9 | Recent Changes & Known Issues | Need a list of recent fixes + open bugs |
| 10 | Quick Reference Card | Need default credentials, URLs, or troubleshooting recipes |

### Build scripts

The DOCX was generated using `python-docx`. Build scripts are kept here for reproducibility:

| Script | Purpose |
|--------|---------|
| `_build_doc_part1.py` | Cover + TOC + Part 1 (Project Overview + Architecture) |
| `_build_doc_part2.py` | Part 3 (Tech Stack) — appends |
| `_build_doc_part3.py` | Part 4 (File Tree) + Part 5 (DB Schema) |
| `_build_doc_part4.py` | Part 6 (Modules 1-7) |
| `_build_doc_part5.py` | Part 6 (Modules 8-15) |
| `_build_doc_part6.py` | Part 7 (Routes) |
| `_build_doc_part7.py` | Part 8 (Business Logic) |
| `_build_doc_part8.py` | Part 9 + Part 10 |
| `_fix_part_numbers.py` | First-pass Part renumbering |
| `_fix_part_numbers_v2.py` | Final Part renumbering |
| `_fix_section_numbers.py` | Section renumbering (H2/H3 scoped per Part) |

### Regenerate from scratch

```bash
cd "C:\MAGZANIV6\Magzani\docs"
python _build_doc_part1.py
python _build_doc_part2.py
python _build_doc_part3.py
python _build_doc_part4.py
python _build_doc_part5.py
python _build_doc_part6.py
python _build_doc_part7.py
python _build_doc_part8.py
python _fix_part_numbers.py
python _fix_part_numbers_v2.py
python _fix_section_numbers.py
```

## When to refresh this documentation

Run a fresh `explore` pass + rebuild whenever:

- A new module is added (e.g. a new controller or Livewire component)
- Database schema changes (new migrations or new columns)
- Plan / feature flag structure changes
- POS shift logic, accounting posting flow, or RBAC seed changes
- New tenant onboarding flow changes

## When NOT to explore the codebase again

For the following common questions, this document is sufficient — no re-exploration needed:

- "What is the URL for X?" → Part 7 (Routes)
- "What columns are in the X table?" → Part 5 (Database Schema)
- "What does the X module do?" → Part 6 (Modules)
- "How does X business flow work?" → Part 8 (Business Logic)
- "What is the default plan / credential?" → Part 10 (Quick Reference)
- "Recent commits / known issues?" → Part 9

---

*Last regenerated: 2026-07-04 (covering Laravel 12 + Stancl Tenancy 3.x stack)*
