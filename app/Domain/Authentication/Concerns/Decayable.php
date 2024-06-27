<?php

namespace App\Domain\Authentication\Concerns;

use App\Domain\Authentication\AuthRateLimiter;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Fortify;

trait Decayable
{
    protected $limiter;

    public function __construct(StatefulGuard $guard, AuthRateLimiter $limiter)
    {
        parent::__construct($guard, $limiter);

        $this->limiter = $limiter;
    }

    protected function throwFailedAuthenticationException($request)
    {
        $this->limiter->increment($request);

        throw ValidationException::withMessages([
            Fortify::username() => [trans('auth.failed')],
        ]);
    }
}
