<?php

namespace App\Domain\Authentication;

use App\Domain\Authentication\Stages\EnsureMaxLoginAttemptsNotExceeded;
use Illuminate\Http\Request;
use Laravel\Fortify\Actions\EnsureLoginIsNotThrottled;
use Laravel\Fortify\Actions\PrepareAuthenticatedSession;
use Laravel\Fortify\Features;

class AuthenticationStages
{
    public static function handle(Request $request)
    {
        return array_filter([
            EnsureMaxLoginAttemptsNotExceeded::class,
            config('fortify.limiters.login') ? null : EnsureLoginIsNotThrottled::class,
            Features::enabled(Features::twoFactorAuthentication()) ? Try2FactorAuthentication::class : null,
            TryToAuthenticate::class,
            PrepareAuthenticatedSession::class,
        ]);
    }
}
