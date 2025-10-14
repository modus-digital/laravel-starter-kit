<?php

use Livewire\Attributes\Layout;
use App\Enums\Settings\TwoFactor;
use App\Enums\Settings\TwoFactorProvider;
use App\Enums\Settings\UserSettings;
use App\Models\User;
use App\Notifications\Auth\TwoFactorVerification;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Volt\Component;
use PragmaRX\Google2FA\Exceptions\IncompatibleWithGoogleAuthenticatorException;
use PragmaRX\Google2FA\Exceptions\InvalidCharactersException;
use PragmaRX\Google2FA\Exceptions\SecretKeyTooShortException;
use PragmaRX\Google2FA\Google2FA;

new #[Layout('components.layouts.guest')] class extends Component {
    private const EMAIL_CODE_TTL_MINUTES = 10;
    private const EMAIL_CODE_MAX_ATTEMPTS = 5;

    public string $code = '';

    public User $user;

    public array $settings = [];

    public ?CarbonImmutable $lastAuthenticatorAttempt = null;

    public string $status = '';

    public function mount(): void
    {
        if (! auth()->check()) {
            $this->redirectRoute('login');

            return;
        }

        $this->user = auth()->user();

        $securitySetting = $this->user->settings->firstWhere('key', UserSettings::SECURITY);

        $this->settings = $securitySetting?->retrieve(UserSettings::SECURITY, 'two_factor') ?? [];

        if (($this->settings['status'] ?? null) !== TwoFactor::ENABLED->value) {
            $this->redirectRoute('app.dashboard');

            return;
        }

        session()->forget('two_factor_verified');

        if (($this->settings['provider'] ?? null) === TwoFactorProvider::EMAIL->value) {
            if (Cache::get($this->emailCodeCacheKey()) === null) {
                $this->sendEmailCode();
            }
        }
    }

    public function verify(): void
    {
        if (! auth()->check()) {
            $this->redirectRoute('login');

            return;
        }

        $provider = $this->settings['provider'] ?? null;

        if ($provider === TwoFactorProvider::EMAIL->value) {
            $this->verifyEmailCode();

            return;
        }

        if ($provider === TwoFactorProvider::AUTHENTICATOR->value) {
            $this->verifyAuthenticatorCode();

            return;
        }

        $this->completeVerification();
    }

    public function resend(): void
    {
        if (($this->settings['provider'] ?? null) !== TwoFactorProvider::EMAIL->value) {
            return;
        }

        $this->status = __('auth.two_factor.email.messages.code_sent');
        $this->sendEmailCode();
    }

    private function sendEmailCode(): void
    {
        $this->resetErrorBag('code');
        $this->code = '';

        $expiresInMinutes = $this->emailCodeExpiresInMinutes();

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        Cache::put(
            key: $this->emailCodeCacheKey(),
            value: $code,
            ttl: now()->addMinutes($expiresInMinutes)
        );

        Cache::put(
            key: $this->emailAttemptsCacheKey(),
            value: 0,
            ttl: now()->addMinutes($expiresInMinutes)
        );

        $this->user->notify(new TwoFactorVerification(
            code: $code,
            expiresInMinutes: $expiresInMinutes
        ));
    }

    private function verifyEmailCode(): void
    {
        $throttleKey = 'two-factor-email:'.auth()->id();

        if (RateLimiter::tooManyAttempts($throttleKey, 3)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $this->addError('code', __('auth.throttle', ['seconds' => $seconds]));
            return;
        }

        $this->resetErrorBag('code');
        $this->code = mb_trim($this->code);

        if ($this->code === '' || ! preg_match('/^\d{6}$/', $this->code)) {
            $this->addError('code', __('auth.two_factor.email.messages.code_invalid'));

            return;
        }

        $cachedCode = Cache::get($this->emailCodeCacheKey());

        if ($cachedCode === null) {
            $this->addError('code', __('auth.two_factor.email.messages.code_expired'));

            return;
        }

        $attempts = (int) Cache::get($this->emailAttemptsCacheKey(), 0);

        if ($attempts >= self::EMAIL_CODE_MAX_ATTEMPTS) {
            $this->clearEmailVerificationState();
            $this->addError('code', __('auth.two_factor.email.messages.code_attempts_exceeded'));

            return;
        }

        if (! hash_equals($cachedCode, $this->code)) {
            RateLimiter::hit($throttleKey, 60);
            Cache::put(
                key: $this->emailAttemptsCacheKey(),
                value: $attempts + 1,
                ttl: now()->addMinutes($this->emailCodeExpiresInMinutes())
            );

            $this->addError('code', __('auth.two_factor.email.messages.code_invalid'));

            return;
        }

        $this->clearEmailVerificationState();
        RateLimiter::clear($throttleKey);

        $this->completeVerification();
    }

    private function verifyAuthenticatorCode(): void
    {
        $this->resetErrorBag('code');
        $this->code = mb_trim($this->code);

        if (! preg_match('/^\d{6}$/', $this->code)) {
            $this->addError('code', __('auth.two_factor.messages.code_invalid'));

            return;
        }

        if ($this->lastAuthenticatorAttempt !== null && now()->subSeconds(15)->lessThan($this->lastAuthenticatorAttempt)) {
            $this->addError('code', __('auth.two_factor.messages.code_recent'));

            return;
        }

        $secret = $this->settings['secret'] ?? null;

        if ($secret === null) {
            $this->addError('code', __('auth.two_factor.messages.code_invalid'));

            return;
        }

        $google2FA = new Google2FA();

        try {
            if (! $google2FA->verifyKey($secret, $this->code)) {
                $this->addError('code', __('auth.two_factor.messages.code_invalid'));
                $this->lastAuthenticatorAttempt = CarbonImmutable::now();

                return;
            }
        } catch (IncompatibleWithGoogleAuthenticatorException|SecretKeyTooShortException|InvalidCharactersException $exception) {
            report($exception);
            $this->addError('code', __('auth.two_factor.messages.code_invalid'));
            $this->lastAuthenticatorAttempt = CarbonImmutable::now();

            return;
        }

        $this->completeVerification();
    }

    private function completeVerification(): void
    {
        session()->put('two_factor_verified', true);

        $this->redirectRoute('app.dashboard');
    }

    private function clearEmailVerificationState(): void
    {
        Cache::forget($this->emailCodeCacheKey());
        Cache::forget($this->emailAttemptsCacheKey());
    }

    private function emailCodeExpiresInMinutes(): int
    {
        return (int) ($this->settings['expires_in_minutes'] ?? self::EMAIL_CODE_TTL_MINUTES);
    }

    private function emailCodeCacheKey(): string
    {
        return sprintf('two_factor:login:email:code:%s', $this->user->getKey());
    }

    private function emailAttemptsCacheKey(): string
    {
        return sprintf('two_factor:login:email:attempts:%s', $this->user->getKey());
    }
};

?>

<div class="flex flex-col items-center justify-center px-6 py-8 mx-auto md:h-screen lg:py-0">
    <x-slot name="title">{{ __('auth.verification.page_title') }}</x-slot>
    <a href="#" class="flex items-center mb-6 text-2xl font-semibold text-zinc-900 dark:text-white">
        <x-application-logo class="w-24 h-24 mr-3 text-zinc-900 dark:text-zinc-50" />
    </a>
    <div class="w-full bg-white rounded-lg shadow dark:border md:mt-0 sm:max-w-md xl:p-0 dark:bg-zinc-800 dark:border-zinc-700">
        <div class="p-6 space-y-4 md:space-y-6 sm:p-8">
            <div class="space-y-2">
                <h1 class="text-xl font-bold leading-tight tracking-tight text-zinc-900 md:text-2xl dark:text-white">
                    {{ __('auth.two_factor.verify.title') }}
                </h1>
                <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-1">
                    {{ __('auth.two_factor.verify.message') }}
                </p>
            </div>

            <form class="space-y-4 md:space-y-6" wire:submit.prevent="verify">
                <input
                    type="text"
                    id="auth-code"
                    wire:model="code"
                    placeholder="{{ __('auth.two_factor.verify.placeholder') }}"
                    maxlength="6"
                    class="w-full px-3 py-2 border border-zinc-300 dark:border-zinc-600 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 dark:bg-zinc-700 dark:text-white text-center text-lg font-mono tracking-widest"
                    inputmode="numeric"
                    pattern="[0-9]*"
                    oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                />
                @error('code')
                    <p class="text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror

                <button type="submit" class="mt-4 w-full text-white bg-primary-600 hover:bg-primary-700 focus:ring-4 focus:outline-none focus:ring-primary-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800">
                    {{ __('auth.two_factor.verify.button') }}
                </button>

                <div class="text-sm flex justify-between">
                    <a href="{{ route('two-factor.recover') }}" class="font-medium text-primary-600 hover:underline dark:text-primary-500">
                        {{ __('auth.two_factor.verify.use_recovery_code') }}
                    </a>

                    <button type="button" wire:click="resend" class="font-medium text-primary-600 hover:underline dark:text-primary-500">
                        {{ __('auth.two_factor.email.messages.code_resend') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
