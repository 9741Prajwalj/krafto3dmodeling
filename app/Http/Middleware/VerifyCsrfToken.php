<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        'page-builder/*',
        '/paytm-payment/status',
        '/jazzcash-payment-status',
        '/payumoney-payment-success',
        '/payumoney-payment-failed',
        '/midtrans-payment-success',
        '/midtrans-payment-failed',
        'install', 'install/*','search',
        'google/refresh-token',
        '/api/save-box-configuration',

//        Start SslComerze
          '/ssl-commerz/success','/ssl-commerz/cancel','/ssl-commerz/fail','/ssl-commerz/ipn',
          "/clickpay-return",
//        End
    ];
}
