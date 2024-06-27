<?php

namespace App\Domain\Authentication;

use Illuminate\Http\Request;
use Laravel\Fortify\LoginRateLimiter;

class AuthRateLimiter extends LoginRateLimiter
{
    const LIMIT = 4;

    const DECAY = 1800;

    public function increment(Request $request): void
    {
        $this->limiter->hit($this->throttleKey($request), self::DECAY);
    }
}
