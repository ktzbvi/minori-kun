<?php

return [
    'driver' => env('BUYER_PAYMENT_DRIVER', in_array(env('APP_ENV', 'production'), ['local', 'testing'], true) ? 'fake' : 'payjp'),
];
