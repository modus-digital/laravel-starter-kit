<?php

declare(strict_types=1);

namespace App\Livewire\User\Profile;

use App\Enums\Settings\TwoFactor as TwoFactorEnum;
use App\Enums\Settings\TwoFactorProvider;
use App\Enums\Settings\UserSettings;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

final class TwoFactor extends Component
{
    public string $status;

    public ?TwoFactorProvider $provider = null;

    /**
     * @var array<string, int>|null
     */
    public ?array $recoveryCodes = null;

    /**
     * @var array<string, string>
     */
    protected $listeners = ['two-factor-updated' => '$refresh'];

    #[On('two-factor-updated')]
    public function mount(): void
    {
        $user = Auth::user();
        $settings = collect($user?->settings->where('key', UserSettings::SECURITY)->first()->value ?? [])->dot();

        $this->status = (string) $settings->get('two_factor.status', TwoFactorEnum::DISABLED->value);
        $provider = $settings->get('two_factor.provider');
        $this->provider = $provider !== null
            ? TwoFactorProvider::from($provider)
            : TwoFactorProvider::EMAIL;

        $recoveryCodes = $settings->filter(function ($_, $key) {
            return str_starts_with($key, 'two_factor.recovery_codes.');
        });

        $this->recoveryCodes = [
            'used' => 8 - $recoveryCodes->count(),
            'total' => 8,
        ];
    }

    public function disable(): void
    {
        $user = Auth::user();

        $setting = $user?->settings->where('key', UserSettings::SECURITY)->first();

        if ($setting === null) {
            return;
        }

        $setting->updateValueAttribute('two_factor', [
            'provider' => null,
            'status' => TwoFactorEnum::DISABLED->value,
            'secret' => null,
            'confirmed_at' => null,
            'recovery_codes' => [],
        ]);

        $this->status = TwoFactorEnum::DISABLED->value;
        $this->provider = TwoFactorProvider::EMAIL;
        $this->recoveryCodes = [
            'used' => 0,
            'total' => 8,
        ];

        $this->dispatch('close-modal', name: 'disable-two-factor-confirmation');
        $this->dispatch('two-factor-updated');
    }

    public function render(): View
    {
        return view('livewire.user.profile.two-factor', [
            'currentStatus' => $this->status,
            'provider' => $this->provider,
        ]);
    }
}
