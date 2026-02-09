<?php

declare(strict_types=1);

namespace App\Events\Security;

use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final readonly class PasswordChanged
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public User $user,
        public ?string $ipAddress = null,
        public ?string $userAgent = null,
    ) {}
}
