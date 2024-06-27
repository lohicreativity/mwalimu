<?php

namespace App\Domain\Authentication\Actions;

use App\Models\User;
use App\Domain\Authentication\Notifications\AccountLockedNotification;

class LockAccountAction
{
    public static function handle(User $user): void
    {
        $user->lockAccount();

        $user->notify(new AccountLockedNotification());
    }
}
