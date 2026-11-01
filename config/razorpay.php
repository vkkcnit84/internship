<?php

/*
| Razorpay credentials. Test -> production is only an .env change: swap the
| rzp_test_* keys for rzp_live_* keys (and the matching webhook secret).
| The mode is derived from the key prefix, so it can never disagree with the keys.
*/
return [
    'key_id'         => env('RAZORPAY_KEY_ID'),
    'key_secret'     => env('RAZORPAY_KEY_SECRET'),
    'webhook_secret' => env('RAZORPAY_WEBHOOK_SECRET'),
    'base_url'       => env('RAZORPAY_BASE_URL', 'https://api.razorpay.com/v1/'),
    'currency'       => 'INR',
    'merchant_name'  => env('RAZORPAY_MERCHANT_NAME', env('APP_NAME', 'Laravel')),
];
