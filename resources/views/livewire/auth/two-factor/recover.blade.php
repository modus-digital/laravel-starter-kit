<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use App\Enums\Settings\UserSettings;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

new #[Layout('components.layouts.guest')] class extends Component {
    public string $recoveryCode = '';

    public function recover() {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $throttleKey = 'two-factor-recovery:'.Auth::id();

        if (RateLimiter::tooManyAttempts($throttleKey, 3)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            throw ValidationException::withMessages([
                'recoveryCode' => __('auth.throttle', ['seconds' => $seconds]),
            ]);
        }

        $this->validate([
            'recoveryCode' => 'required|string',
        ]);

        $recoveryCodes = $this->getRecoveryCodes();

        if ($recoveryCodes === null) {
            RateLimiter::hit($throttleKey, 60);
            $this->addError('recoveryCode', __('auth.two_factor.recover.invalid'));
            return;
        }

        // Normalize the recovery code
        $recoveryCode = $this->recoveryCode;

        // If the code does not contain a dash, insert one in the middle
        if (strpos($recoveryCode, '-') === false && strlen($recoveryCode) > 0) {
            $length = strlen($recoveryCode);
            $half = (int) ceil($length / 2);
            $recoveryCode = substr($recoveryCode, 0, $half) . '-' . substr($recoveryCode, $half);
        }

        if (!in_array($recoveryCode, $recoveryCodes, true)) {
            RateLimiter::hit($throttleKey, 60);
            $this->addError('recoveryCode', __('auth.two_factor.recover.invalid'));
            return;
        }

        $recoveryCodes = array_diff($recoveryCodes, [$recoveryCode]);

        $this->updateRecoveryCodes($recoveryCodes);

        RateLimiter::clear($throttleKey);
        session(['two_factor_verified' => true]);

        return to_route('app.dashboard');
    }

    private function getRecoveryCodes(): ?array {
        $user = Auth::user();

        if (!$user) {
            return null;
        }

        $securitySetting = $user
            ->settings()
            ->where('key', UserSettings::SECURITY)
            ->first();

        if (!$securitySetting) {
            return null;
        }

        $twoFactorSettings = $securitySetting->retrieve(UserSettings::SECURITY, 'two_factor');

        if (!$twoFactorSettings || !isset($twoFactorSettings['recovery_codes'])) {
            return null;
        }

        return $twoFactorSettings['recovery_codes'];
    }

    private function updateRecoveryCodes(array $recoveryCodes): void {
        $user = Auth::user();

        if (!$user) {
            return;
        }

        $twoFactorSettings = $user->settings()->where('key', UserSettings::SECURITY)->first();

        if (!$twoFactorSettings) {
            return;
        }

        $twoFactorSettings->updateValueAttribute(path: 'two_factor.recovery_codes', value: $recoveryCodes);
    }
};
?>

<div class="flex flex-col items-center justify-center px-6 py-8 mx-auto md:h-screen lg:py-0">
    <x-slot name="title">{{ __('auth.two_factor.recover.title') }}</x-slot>
    <a href="#" class="flex items-center mb-6 text-2xl font-semibold text-zinc-900 dark:text-white">
        <x-application-logo class="text-zinc-900 dark:text-zinc-50" variant="white" />
    </a>
    <div class="w-full bg-white rounded-lg shadow dark:border md:mt-0 sm:max-w-md xl:p-0 dark:bg-zinc-800 dark:border-zinc-700">
        <div class="p-6 space-y-4 md:space-y-6 sm:p-8">
            <div class="space-y-2">
                <h1 class="text-xl font-bold leading-tight tracking-tight text-zinc-900 md:text-2xl dark:text-white">
                    {{ __('auth.two_factor.recover.title') }}
                </h1>
                <p class="text-base text-zinc-500 dark:text-zinc-400 mt-1">
                    {{ __('auth.two_factor.recover.description') }}
                </p>
            </div>

            <form class="space-y-4 md:space-y-6" wire:submit="recover">
                <input
                    type="text"
                    wire:model="recoveryCode"
                    placeholder="{{ __('auth.two_factor.recover.placeholder') }}"
                    maxlength="9"
                    oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                    class="w-full px-3 py-2 border border-zinc-300 dark:border-zinc-600 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 dark:bg-zinc-700 dark:text-white"
                />

                @error('recoveryCode')
                    <p class="text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror

                <button type="submit" class="mt-4 w-full text-black bg-orange-600 hover:bg-orange-700 focus:ring-4 focus:outline-none focus:ring-orange-300 font-medium rounded-lg text-base px-5 py-2.5 text-center dark:bg-orange-600 dark:hover:bg-orange-700 dark:focus:ring-orange-800">
                    {{ __('auth.two_factor.recover.button') }}
                </button>
            </form>
        </div>
    </div>
</div>
