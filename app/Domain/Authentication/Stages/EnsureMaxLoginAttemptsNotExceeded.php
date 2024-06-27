<?php

namespace App\Domain\Authentication\Stages;

use App\Models\User;
use App\Domain\Authentication\Actions\LockAccountAction;
use App\Domain\Authentication\AuthRateLimiter;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\LockoutResponse;
use Laravel\Fortify\Fortify;

class EnsureMaxLoginAttemptsNotExceeded
{
    protected $limiter;

    public function __construct(AuthRateLimiter $limiter)
    {
        $this->limiter = $limiter;
    }

    public function handle($request, $next)
    {
        $user = $this->getUser($request->email);

        if (filled($user) && $user->isEmployee() && $user->isNotActive()) {
            return throw ValidationException::withMessages([
                Fortify::username() => ['Your account has been locked. For assistance, please contact System Administrator.'],
            ]);
        }

        if ($this->limiter->attempts($request) >= AuthRateLimiter::LIMIT) {

            if (filled($user) && $user->isEmployee()) {

                if ($user->isActive()) {
                    LockAccountAction::handle($user);
                }

                return throw ValidationException::withMessages([
                    Fortify::username() => ['Your account has been locked. For assistance, please contact System Administrator.'],
                ]);
            }

            return app(LockoutResponse::class);
        }

        return $next($request);
    }

    protected function lockUserAccount($request): void
    {
        $user = $this->getUser($request->email);

        if (blank($user)) {
            return;
        }

        if ($user->isEmployee()) {
            LockAccountAction::handle($user);
        }
    }

    protected function getUser(string $email): ?User
    {
        return User::firstWhere('email', $email);
    }
}
