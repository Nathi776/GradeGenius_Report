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

];
