<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Pricing Page Configuration
    |--------------------------------------------------------------------------
    |
    | Settings used by app/Http/Controllers/PricingController.php and its
    | associated views (resources/views/pricing/*). All values can be overridden
    | via .env so the same codebase can be deployed to different branded
    | subdomains (e.g. pricing.kayyan.com, pricing.remotelly1.site) without
    | code changes.
    |
    */

    // Booking / demo link — defaults to a Google Calendar public URL pattern.
    // Override via KAYYAN_DEMO_URL if you use Calendly, Cal.com, etc.
    'demo_url' => env('KAYYAN_DEMO_URL', 'https://calendar.google.com/calendar/appointments/schedules'),

    // Signup / free-trial URL — where the hero CTA points.
    // Override via KAYYAN_SIGNUP_URL.
    'signup_url' => env('KAYYAN_SIGNUP_URL', env('APP_URL', 'http://localhost') . '/super-admin/tenants/create'),

    // Brand name + tagline (used in <title>, JSON-LD, social cards)
    'brand_name'    => env('KAYYAN_BRAND_NAME', 'كيان SaaS'),
    'brand_tagline' => env('KAYYAN_BRAND_TAGLINE', 'نظام إدارة الأعمال والمخازن الذكي'),
    'brand_locale'  => env('KAYYAN_BRAND_LOCALE', 'ar_SA'),

    // SEO defaults
    'seo_title'       => env('KAYYAN_SEO_TITLE', 'الأسعار | كيان SaaS - نظام إدارة الأعمال الذكي'),
    'seo_description' => env('KAYYAN_SEO_DESCRIPTION', 'اختر باقة كيان SaaS المناسبة لنمو أعمالك. باقات مرنة تبدأ من 99 ج.م شهرياً تشمل نقاط البيع، التصنيع، المحاسبة المتقدمة، والتقارير المالية.'),
    'seo_keywords'    => env('KAYYAN_SEO_KEYWORDS', 'نظام إدارة أعمال, نقاط بيع, محاسبة, تصنيع, مخازن, فواتير, كيان SaaS, Magzani, ERP, POS'),
];
