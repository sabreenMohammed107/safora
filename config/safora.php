<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Billing
    |--------------------------------------------------------------------------
    |
    | Prices are stored and quoted in SAR. VAT is added on top of the
    | discounted subtotal (docs/sprints/03_BUSINESS_RULES.md §1).
    |
    */

    'currency' => 'SAR',

    'vat_rate' => (float) env('SAFORA_VAT_RATE', 0.15),

    /*
    |--------------------------------------------------------------------------
    | Languages
    |--------------------------------------------------------------------------
    |
    | Every screen ships in Arabic (RTL) and English (LTR). The first entry
    | is the default for guests (docs/sprints/06_I18N.md).
    |
    */

    'locales' => [
        'ar' => ['name' => 'العربية', 'dir' => 'rtl'],
        'en' => ['name' => 'English', 'dir' => 'ltr'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Initial Administrator
    |--------------------------------------------------------------------------
    |
    | Used by AdminSeeder. A password must be set explicitly in production.
    |
    */

    'admin' => [
        'name' => env('SAFORA_ADMIN_NAME', 'مدير النظام'),
        'email' => env('SAFORA_ADMIN_EMAIL', 'admin@safora.test'),
        'password' => env('SAFORA_ADMIN_PASSWORD'),
    ],

];
