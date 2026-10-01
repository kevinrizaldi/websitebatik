<?php

return [
    'merchant_id' => env('MIDTRANS_MERCHANT_ID'),
    'server_key' => env('MIDTRANS_SERVER_KEY', 'SB-Mid-server-sandbox-demo-key'),
    'client_key' => env('MIDTRANS_CLIENT_KEY', 'SB-Mid-client-sandbox-demo-key'),
    'is_production' => (bool) env('MIDTRANS_IS_PRODUCTION', false),
    'is_sanitized' => true,
    'is_3ds' => true,

    'snap_url' => env('MIDTRANS_IS_PRODUCTION', false)
        ? 'https://app.midtrans.com/snap/v1/transactions'
        : 'https://app.sandbox.midtrans.com/snap/v1/transactions',

    'snap_js_url' => env('MIDTRANS_IS_PRODUCTION', false)
        ? 'https://app.midtrans.com/snap/snap.js'
        : 'https://app.sandbox.midtrans.com/snap/snap.js',
];
