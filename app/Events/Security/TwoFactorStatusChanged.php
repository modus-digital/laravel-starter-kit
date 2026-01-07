<?php

declare(strict_types=1);

namespace App\Events\Security;

use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class TwoFactorStatusChanged
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly User $user,
        public readonly bool $enabled,
        public readonly ?string $ipAddress = null,
        public readonly ?string $userAgent = null,
    ) {}
}
