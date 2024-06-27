<?php

namespace App\Domain\Authentication;

use App\Domain\Authentication\Concerns\Decayable;
use Laravel\Fortify\Actions\AttemptToAuthenticate;

class TryToAuthenticate extends AttemptToAuthenticate
{
    use Decayable;
}
