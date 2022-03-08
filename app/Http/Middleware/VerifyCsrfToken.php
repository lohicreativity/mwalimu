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
        'bills/post_bill',
        'gepg/bill',
        'gepg/receipt',
        'gepg/reconcile',
        'response/gepg/bill',
        'response/gepg/receipt',
        'response/gepg/reconcile',
        'finance/post-reconciliation'
    ];
}
