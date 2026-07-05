# Kayyan SaaS — Public Pricing Page

> Standalone marketing pricing page deployed at **pricing.kayyan.com** (production) and **http://localhost/pricing** (local dev).
> All marketing content is **hard-coded in the Blade view** (`resources/views/pricing/index.blade.php`) — edit the view directly to change hero text, features list, addons, FAQs, etc.
> The landlord dashboard controls plans via `/super-admin/plans/*` (existing CRUD).
> WhatsApp CTAs have been removed from the page; the only CTA per card is now **"احجز ديمو"** pointing to the configured demo URL.

## Quick Facts

| Attribute | Value |
|----------|-------|
| Route URL | `GET /pricing` (public, no auth) |
| Route name | `pricing.public` |
| Controller | `App\Http\Controllers\PricingController@index` |
| View | `resources/views/pricing/index.blade.php` (extends `pricing.layout`) |
| Tenant context | ❌ No (always uses central DB connection) |
| Subdomain config | Add to `CENTRAL_DOMAINS` env var (comma-separated) |

---

## File Inventory (final state — 2026-07-04)

### New files (pricing page)
- `app/Http/Controllers/PricingController.php` — fetches plans, returns view
- `config/pricing.php` — env-driven config (demo URL, signup URL, brand name, SEO defaults)
- `resources/views/pricing/layout.blade.php` — standalone RTL layout (SVG defs + critical CSS + SEO meta)
- `resources/views/pricing/index.blade.php` — 9-section marketing page (hard-coded content)
- `docs/PRICING_PAGE.md` — this document

### Modified files
- `routes/web.php` — added `GET /pricing` route
- `.env.example` — added KAYYAN_DEMO_URL / KAYYAN_SIGNUP_URL / KAYYAN_BRAND_* env vars documentation
- `.env.production.example` — added `pricing.kayyan.com` to `CENTRAL_DOMAINS` + KAYYAN_* values
- `resources/views/Dashboard/dashboard.blade.php` — added conditional **Upgrade Banner** (shows for starter/pro tenants)

---

## Why No CMS? (UX Decision)

A separate CMS at `/super-admin/pricing-settings` was prototyped and then **removed** per operator preference:
- The operator controls everything from `/super-admin/plans` (existing CRUD for plans/slugs/prices/features).
- Marketing copy (hero, trust stats, features grid, addons, FAQs) is hard-coded directly in `resources/views/pricing/index.blade.php`.
- To tweak the page copy, edit the view file directly — it's fully self-contained.

If a CMS becomes desirable later, it can be re-added by re-introducing the `PricingSetting` model + `pricing-settings` view/controller pattern that was previously removed.

---

## To Edit Marketing Content

Open `resources/views/pricing/index.blade.php` in your editor. All section copy is hard-coded via `@php` arrays at the top of each section:

| Section | Editable content | Lines (approx) |
|---------|------------------|-----------------|
| Top bar | No content — just the brand bar | 1-30 |
| Hero | `نظام SaaS متعدد المستأجرين` eyebrow, headline, subtitle, CTA labels, trust badges (4 spans) | 33-90 |
| Trust strip | 4 stats in `$stats` array (icon + value + label) | 95-115 |
| Features grid | 8 features in `$features` array (icon + color + title + desc) | 120-160 |
| Pricing cards | Per-plan value-props hard-coded in `PricingController::PLAN_VALUE_PROPS` constant | controller file |
| Add-ons | 4 addons in `$addons` array | 215-250 |
| FAQ | 6 Q&A pairs in `$faqs` array | 260-300 |
| Final CTA | "ابدأ تجربتك المجانية الآن" + footer copyright | 305-340 |

After editing, hit **Ctrl+S** in your editor — no artisan command needed (views are recompiled on demand).

---

## Routing Placement

The route lives in `routes/web.php`, NOT `routes/tenant.php`. This is critical:

```
routes/web.php is wrapped by RouteServiceProvider with:
  → 'web' middleware group (sessions, CSRF, etc.)
  → 'central.domains' middleware (CentralDomainsOnly)

Since 'central.domains' allows requests ONLY from hosts in
`config('tenancy.central_domains')`, and Stancl tenancy bootstrappers
are NEVER applied to web.php, the pricing page:
  ✓ Reads from the CENTRAL DB (default DB_CONNECTION)
  ✓ Returns 404 on any tenant subdomain
  ✓ Is fully cacheable / SEO-indexable
```

### Subdomain Setup (Production)

To deploy on a dedicated subdomain like `pricing.kayyan.com`:

1. **DNS**: point `pricing.kayyan.com` A/CNAME to the same server as your main dashboard host
2. **Web server**: serve the same `public/` document root for both domains (Nginx/Apache vhost)
3. **`.env`**: extend `CENTRAL_DOMAINS` to include the new subdomain:
   ```env
   CENTRAL_DOMAINS=superdashboard.remotelly1.site,pricing.kayyan.com
   ```
4. **SSL**: ensure the subdomain has a valid HTTPS certificate
5. **Test**: `curl -I https://pricing.kayyan.com/pricing` should return 200

### Data Flow

```
Browser → Server (pricing.kayyan.com)
        → routes/web.php → central.domains middleware (allows)
        → PricingController@index
            → Plan::where('is_active', true)
                  ->whereIn('slug', ['starter','pro','enterprise'])
                  ->with(['featuresList' => enabled])->get()
            → Returns view('pricing.index', compact('plans'))
        → pricing/layout.blade.php (HTML shell, SEO meta, JSON-LD)
        → pricing/index.blade.php (9 sections, hard-coded)
        → Rendered HTML (~76 KB, fully responsive)
```

---

## Page Sections (Top → Bottom)

| # | Section | Purpose |
|---|---------|---------|
| 1 | **Sticky Top Bar** | Kayyan K-logo + nav links + Start Free Trial CTA |
| 2 | **Hero** | H1 headline + subtitle + dual CTAs + 4 trust badges |
| 3 | **Trust Strip** | 4 stats (+500 شركات, +2,400 مستخدم, +1.2M فاتورة, 99.9% uptime) |
| 4 | **8 Features Grid** | POS / المخازن / التصنيع / المحاسبة / التقارير / RBAC / مخزون الخشب / الدعم |
| 5 | **Pricing Cards** | 3 cards (Starter/Pro/Enterprise) — each with "احجز ديمو مجاني" CTA. Pro has "الأكثر طلباً" badge with animated pulse. |
| 6 | **Comparison Table** | 8-row feature matrix (desktop) + accordion view (mobile) |
| 7 | **Add-ons** | 4 mini-cards: تدريب / دعم VIP / تخصيص / ترحيل بيانات |
| 8 | **FAQ** | 6 collapsible `<details>` Q&As (zero JS) |
| 9 | **Final CTA + Footer** | Single primary CTA + footer with social icons |

---

## Customization via `.env`

Brand URLs and SEO can still be overridden via env:

```env
# Booking/demo link
KAYYAN_DEMO_URL=https://calendar.google.com/calendar/appointments/schedules

# Where the "Start Free Trial" CTA points
KAYYAN_SIGNUP_URL=https://pricing.kayyan.com/super-admin/tenants/create

# Brand overrides (optional)
KAYYAN_BRAND_NAME="كيان SaaS"
KAYYAN_BRAND_TAGLINE="نظام إدارة الأعمال والمخازن الذكي"

# SEO overrides
KAYYAN_SEO_TITLE="الأسعار | كيان SaaS - نظام إدارة الأعمال الذكي"
KAYYAN_SEO_DESCRIPTION="..."
KAYYAN_SEO_KEYWORDS="..."
```

To change marketing copy (hero, features, FAQs, etc.), edit `resources/views/pricing/index.blade.php` directly.

---

## Updating Plan Data

Plans are managed from the **landlord dashboard**: `/super-admin/plans/*`.

The list of public-page plan slugs is fixed in `PricingController::PUBLIC_PLAN_SLUGS = ['starter', 'pro', 'enterprise']`. To show more plans, edit that constant.

---

## Removed: WhatsApp Contact Channel

This commit history has WhatsApp CTAs fully removed from the page:

| Location | Status |
|----------|--------|
| Top bar | No contact CTA — only "ابدأ تجربتك" remains |
| Hero | Secondary CTA is "احجز ديمو" → demo URL (no WhatsApp) |
| Each pricing card | Single CTA: **"احجز ديمو مجاني"** → demo URL |
| Final CTA | Single primary CTA only (no WhatsApp) |
| Footer social | Twitter + Booking icon (no WhatsApp) |

---

## SEO Checklist (Production)

Before going live:

- [ ] Add `<link rel="icon" href="...">` to `/favicon.ico` (already in layout)
- [ ] Create `/public/og-image.png` (1200x630px) with brand logo + tagline
- [ ] Create `/public/apple-touch-icon.png`
- [ ] Set up Google Search Console + Bing Webmaster Tools
- [ ] Add `pricing.kayyan.com` to Google Analytics / Tag Manager
- [ ] Submit sitemap including `/pricing`
- [ ] Test OG preview with Facebook Sharing Debugger + Twitter Card Validator
- [ ] Verify JSON-LD with Google's Rich Results Test
- [ ] Ensure HTTPS is enforced (HSTS header)

---

## In-App Integration: Upgrade Banner

A conditional **Upgrade Banner** is displayed at the top of the tenant dashboard:
- **Location**: `resources/views/Dashboard/dashboard.blade.php` (top of `<div class="dash-v3">`)
- **Trigger**: shows only for tenants whose `plan_id` is `starter` or `pro`
- **Target**: opens the public pricing subdomain in a new tab (`KAYYAN_SIGNUP_URL` or `https://pricing.kayyan.com`)

---

## Quick Links

- Route file: `routes/web.php` (search for `pricing.public`)
- Controller: `app/Http/Controllers/PricingController.php`
- Layout: `resources/views/pricing/layout.blade.php`
- Page: `resources/views/pricing/index.blade.php`
- Config: `config/pricing.php`
- Banner: `resources/views/Dashboard/dashboard.blade.php`
- Plans admin: `/super-admin/plans`

---

_Last updated: 2026-07-04 — pricing page final state (hard-coded content, WhatsApp removed, no CMS)_
