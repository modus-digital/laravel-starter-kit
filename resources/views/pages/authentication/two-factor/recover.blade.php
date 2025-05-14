<?php

use function Laravel\Folio\{name, middleware};
use Livewire\Volt\Component;
use Livewire\Attributes\Validate;
use PragmaRX\Google2FA\Google2FA;
use App\Enums\UserSettings;

name('auth.two-factor.recover');

new class extends Component {
    public string $recoveryCode = '';

    public function recover() {
        $this->validate();

        $recoveryCodes = $this->getRecoveryCodes();

        if (!in_array($this->recoveryCode, $recoveryCodes)) {
            $this->addError('recoveryCode', __('auth.two_factor.recovery.invalid'));
            return;
        }

        // split the recovery code into two parts in the middle
        $recoveryCode = explode('-', $this->recoveryCode);

        $recoveryCodes = array_diff($recoveryCodes, [$recoveryCode]);

        $this->updateRecoveryCodes($recoveryCodes);
        session(['two_factor_verified' => true]);

        return to_route('application.dashboard');
    }

    private function getRecoveryCodes() {
        $user = auth()->user();

        if (!$user) {
            return [];
        }

        $twoFactorSettings = $user
            ->settings()
            ->where('key', UserSettings::SECURITY)
            ->first()
            ->retrieve(UserSettings::SECURITY, 'two_factor');

        return $twoFactorSettings['recovery_codes'];
    }

    private function updateRecoveryCodes(array $recoveryCodes) {
        $user = auth()->user();

        if (!$user) {
            return;
        }

        $twoFactorSettings = $user->settings()->where('key', UserSettings::SECURITY)->first();
        $twoFactorSettings->updateValueAttribute(path: 'two_factor.recovery_codes', newValue: $recoveryCodes);
    }
}

?>

<x-layouts.guest>
    @volt('auth.two-factor.recover')
    <div class="flex flex-col items-center justify-center px-6 py-8 mx-auto md:h-screen lg:py-0">
        <a href="#" class="flex items-center mb-6 text-2xl font-semibold text-gray-900 dark:text-white">
            <x-application-logo class="w-24 h-24 mr-3 text-gray-900 dark:text-gray-50" />
        </a>
        <div class="w-full bg-white rounded-lg shadow dark:border md:mt-0 sm:max-w-md xl:p-0 dark:bg-gray-800 dark:border-gray-700">
            <div class="p-6 space-y-4 md:space-y-6 sm:p-8">
                <div class="space-y-2">
                    <h1 class="text-xl font-bold leading-tight tracking-tight text-gray-900 md:text-2xl dark:text-white">
                        {{ __('auth.two_factor.recover.title') }}
                    </h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        {{ __('auth.two_factor.recover.description') }}
                    </p>
                </div>

                <form class="space-y-4 md:space-y-6" wire:submit="recover">
                    <x-pin-input length="8" wire:model="recoveryCode" size="sm" container="w-full justify-around" alphaNumeric separator />

                    <button type="submit" class="mt-4 w-full text-white bg-primary-600 hover:bg-primary-700 focus:ring-4 focus:outline-none focus:ring-primary-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800">
                        {{ __('auth.two_factor.recover.button') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
    @endvolt
</x-layouts.guest>
