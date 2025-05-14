<?php

namespace App\Livewire\Profile\TwoFactor;

use App\Enums\Settings\UserSettings;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use Livewire\Component;
use Masmerise\Toaster\Toastable;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RegenerateBackupCodes extends Component
{
    use Toastable;

    public array $backupCodes;

    public ?Authenticatable $user;

    public function mount(?Authenticatable $user = null): void
    {
        $this->user = $user;
    }

    #[On('regenerate-backup-codes')]
    public function regenerateBackupCodes(): void
    {
        $this->success(
            message: __('notifications.toasts.two_factor.backup_codes_regenerated'),
        );

        $backupCodes = [];

        for ($i = 0; $i < 10; $i++) {
            $backupCodes[] = Str::upper(
                value: Str::random(length: 4) . '-' . Str::random(length: 4)
            );
        }

        $this->backupCodes = $backupCodes;

        $twoFactorSettings = $this->user->settings()->where('key', UserSettings::SECURITY)->first();
        $twoFactorSettings->updateValueAttribute(
            path: 'two_factor.recovery_codes',
            newValue: $backupCodes
        );
    }

    public function downloadBackupCodes(): StreamedResponse
    {
        $this->success(
            message: __('notifications.toasts.two_factor.backup_codes_downloaded'),
        );

        return download_backup_codes(
            filename: Str::slug(title: config('app.name') . ' Two Factor Backup Codes', separator: '_') . '.txt',
            backupCodes: $this->backupCodes
        );
    }

    public function render()
    {
        return view('livewire.profile.two-factor.regenerate-backup-codes');
    }
}
