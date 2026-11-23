<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Demo report data fallback
    |--------------------------------------------------------------------------
    |
    | When a learner has no recorded marks yet, show the built-in sample report instead of an empty
    | state. Useful for demos and local development; always off in production unless forced on.
    |
    */

    'demo_fallback' => (bool) env('REPORTS_DEMO_FALLBACK', env('APP_ENV', 'production') !== 'production'),

    /*
    |--------------------------------------------------------------------------
    | Planned menu items
    |--------------------------------------------------------------------------
    |
    | Menu entries for pages that are not built yet render as disabled "Soon" or "Roadmap" items.
    | They are shown in development and hidden in production unless forced on.
    |
    */

    'show_planned_menu_items' => (bool) env('REPORTS_SHOW_PLANNED_MENU', env('APP_ENV', 'production') !== 'production'),

];
