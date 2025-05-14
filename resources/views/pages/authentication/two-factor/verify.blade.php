<?php

use function Laravel\Folio\{name, middleware};
use Livewire\Volt\Component;
use Livewire\Attributes\Validate;
use PragmaRX\Google2FA\Google2FA;
use App\Enums\Settings\UserSettings;

name('auth.two-factor.verify');

new class extends Component {

    #[Validate('required')]
    public string $code = '';

    public function mount() {
        $twoFactorSettings = $this->getTwoFactorSettings();
    }

    public function verify() {
        $this->validate();

        $g2fa = new Google2FA;
        $settings = $this->getTwoFactorSettings();

        try {
            $valid = $g2fa->verifyKey($settings['secret'], $this->code);

            if ($valid) {
                session(['two_factor_verified' => true]);

                return to_route('application.dashboard');
            }

            $this->addError('code', __('auth.two_factor.verify.invalid_code'));
        } catch (Exception $e) {
            $this->addError('code', __('auth.two_factor.verify.invalid_code'));
        }
    }

    private function getTwoFactorSettings(): array
    {
        if (!auth()->check()) {
            return [];
        }

        $user = auth()->user();
        $twoFactorSettings = $user
            ->settings()
            ->where('key', UserSettings::SECURITY)
            ->first()
            ->retrieve(UserSettings::SECURITY, 'two_factor');

        return $twoFactorSettings;
    }
}

?>

<x-layouts.guest>
    @volt('auth.two-factor.verify')
    <div class="flex flex-col items-center justify-center px-6 py-8 mx-auto md:h-screen lg:py-0">
        <a href="#" class="flex items-center mb-6 text-2xl font-semibold text-gray-900 dark:text-white">
            <x-application-logo class="w-24 h-24 mr-3 text-gray-900 dark:text-gray-50" />
        </a>
        <div class="w-full bg-white rounded-lg shadow dark:border md:mt-0 sm:max-w-md xl:p-0 dark:bg-gray-800 dark:border-gray-700">
            <div class="p-6 space-y-4 md:space-y-6 sm:p-8">
                <div class="space-y-2">
                    <h1 class="text-xl font-bold leading-tight tracking-tight text-gray-900 md:text-2xl dark:text-white">
                        {{ __('auth.two_factor.verify.title') }}
                    </h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        {{ __('auth.two_factor.verify.message') }}
                    </p>
                </div>

                <form class="space-y-4 md:space-y-6" wire:submit.prevent="verify">
                    <x-pin-input length="6" wire:model="code" size="md" container="w-full justify-between" />

                    <button type="submit" class="mt-4 w-full text-white bg-primary-600 hover:bg-primary-700 focus:ring-4 focus:outline-none focus:ring-primary-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800">
                        {{ __('auth.two_factor.verify.button') }}
                    </button>

                    <div class="text-sm text-center">
                        <a href="{{ route('auth.two-factor.recover') }}" class="font-medium text-primary-600 hover:underline dark:text-primary-500">
                            {{ __('auth.two_factor.verify.use_recovery_code') }}
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endvolt
</x-layouts.guest>
