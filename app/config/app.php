<?php 

return [
   'env' => env('APP_ENV', 'testing'),


   'providers' => [
    //paystack
    Unicodeveloper\Paystack\PaystackServiceProvider::class,
    
],
    
    'aliases' => [
    //
    'Paystack' => Unicodeveloper\Paystack\Facades\Paystack::class,
    //
]
];

