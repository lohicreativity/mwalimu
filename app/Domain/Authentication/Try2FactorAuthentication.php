<?php

namespace App\Domain\Authentication;

use App\Domain\Authentication\Concerns\Decayable;
use Laravel\Fortify\Actions\RedirectIfTwoFactorAuthenticatable;

class Try2FactorAuthentication extends RedirectIfTwoFactorAuthenticatable
{
    use Decayable;
}
