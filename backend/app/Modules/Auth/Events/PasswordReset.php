<?php

declare(strict_types=1);

namespace App\Modules\Auth\Events;

use App\Models\User;

final class PasswordReset
{
    public function __construct(public readonly User $user) {}
}
