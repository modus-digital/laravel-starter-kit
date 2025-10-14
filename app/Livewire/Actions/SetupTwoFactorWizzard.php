<?php

declare(strict_types=1);

namespace App\Livewire\Actions;

use App\Enums\Settings\TwoFactor;
use App\Enums\Settings\TwoFactorProvider;
use App\Enums\Settings\UserSettings;
use App\Livewire\User\Profile\TwoFactor as ProfileTwoFactor;
use App\Models\User;
use App\Notifications\Auth\TwoFactorVerification;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Masmerise\Toaster\Toastable;
use PragmaRX\Google2FA\Exceptions\IncompatibleWithGoogleAuthenticatorException;
use PragmaRX\Google2FA\Exceptions\InvalidCharactersException;
use PragmaRX\Google2FA\Exceptions\SecretKeyTooShortException;
use PragmaRX\Google2FAQRCode\Google2FA;

final class SetupTwoFactorWizzard extends Component
{
    use Toastable;

    private const EMAIL_CODE_TTL_MINUTES = 10;

    private const EMAIL_CODE_MAX_ATTEMPTS = 5;

    public int $currentStep = 1;

    public User $user;

    public ?string $provider = null;

    public string $verificationCode = '';

    public string $qrCodeUrl = '';

    public string $secret = '';

    /**
     * @var array<int, string>
     */
    public array $recoveryCodes = [];

    public bool $isEmailCodeVerified = false;

    public bool $isAuthenticatorVerified = false;

    public ?CarbonImmutable $lastAuthenticatorAttempt = null;

    /**
     * @var array<int|string, string>
     */
    protected $listeners = ['resetWizard'];

    public function mount(): void
    {
        $user = Auth::user();

        if ($user === null) {
            abort(401);
        }

        $this->user = $user;
    }

    public function selectProvider(string $provider): void
    {
        if ($provider !== $this->provider) {
            $this->clearEmailVerificationState();
            $this->isEmailCodeVerified = false;
            $this->isAuthenticatorVerified = false;
            $this->lastAuthenticatorAttempt = null;
            $this->verificationCode = '';
            $this->recoveryCodes = [];
            $this->resetErrorBag('verificationCode');
        }

        $this->provider = $provider;

        if ($this->provider === TwoFactorProvider::AUTHENTICATOR->value) {
            $this->setupAuthenticator();
        } else {
            $this->secret = '';
            $this->qrCodeUrl = '';
        }

        $this->currentStep = 2;
    }

    public function setupAuthenticator(): void
    {
        $authenticatorClient = new Google2FA();

        $this->secret = $authenticatorClient->generateSecretKey();

        $qrCodeUrl = $authenticatorClient->getQRCodeUrl(
            company: config('app.name'),
            holder: $this->user->email,
            secret: $this->secret
        );

        $qrWriterBackend = new Writer(
            renderer: new ImageRenderer(
                rendererStyle: new RendererStyle(size: 250, margin: 1),
                imageBackEnd: new SvgImageBackEnd()
            )
        );

        $this->qrCodeUrl = $qrWriterBackend->writeString($qrCodeUrl);

        $pattern = '/(<svg\b[^>]*)(>)/i';
        $replacement = '$1 class="mx-auto rounded-lg"$2';
        $this->qrCodeUrl = preg_replace($pattern, $replacement, $this->qrCodeUrl) ?? '';

        $this->verificationCode = '';
        $this->resetErrorBag('verificationCode');
        $this->isAuthenticatorVerified = false;
        $this->lastAuthenticatorAttempt = null;

    }

    public function sendEmailCode(): void
    {
        if ($this->provider !== TwoFactorProvider::EMAIL->value) {
            return;
        }

        $this->clearEmailVerificationState();

        $code = mb_str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        Cache::put(
            key: $this->emailCodeCacheKey(),
            value: $code,
            ttl: now()->addMinutes(self::EMAIL_CODE_TTL_MINUTES)
        );

        Cache::put(
            key: $this->emailAttemptsCacheKey(),
            value: 0,
            ttl: now()->addMinutes(self::EMAIL_CODE_TTL_MINUTES)
        );

        Notification::send($this->user, new TwoFactorVerification(
            code: $code,
            expiresInMinutes: self::EMAIL_CODE_TTL_MINUTES
        ));

        $this->success(__('auth.two_factor.email.messages.code_sent'));
    }

    public function verifyCode(): void
    {
        if ($this->provider === TwoFactorProvider::EMAIL->value) {
            $this->verifyEmailCode();

            return;
        }

        if ($this->provider === TwoFactorProvider::AUTHENTICATOR->value) {
            $this->verifyAuthenticatorCode();

            return;
        }
    }

    /**
     * @return array<int, string>
     */
    public function generateRecoveryCodes(): array
    {
        $this->recoveryCodes = collect(range(1, 8))
            ->map(fn () => Str::upper(Str::random(4).'-'.Str::random(4)))
            ->toArray();

        return $this->recoveryCodes;
    }

    public function downloadRecoveryCodes(): void
    {
        if ($this->recoveryCodes === []) {
            $this->generateRecoveryCodes();
        }

        $this->dispatch(
            'download-recovery-codes',
            filename: Str::slug(config('app.name').' Two Factor Recovery Codes', '_').'.txt',
            codes: collect($this->recoveryCodes)->map(static fn (string $code) => $code)->values()->all(),
        );

        $this->success(__('auth.two_factor.messages.recovery_download'));
    }

    public function confirmSetup(): void
    {
        if ($this->provider === TwoFactorProvider::EMAIL->value && ! $this->isEmailCodeVerified) {
            $this->currentStep = 2;
            $this->addError('verificationCode', __('auth.two_factor.email.messages.code_unverified'));

            return;
        }

        if ($this->provider === TwoFactorProvider::AUTHENTICATOR->value && ! $this->isAuthenticatorVerified) {
            $this->currentStep = 2;
            $this->addError('verificationCode', __('auth.two_factor.messages.code_unverified'));

            return;
        }

        if ($this->recoveryCodes === []) {
            $this->generateRecoveryCodes();
        }

        $this->persistTwoFactorSettings();

        $this->dispatch('two-factor-updated')->to(ProfileTwoFactor::class);

        $this->success(__('auth.two_factor.messages.enabled'));

        $this->dispatch('close-modal');

        $this->resetWizard();
    }

    public function nextStep(): void
    {
        if ($this->currentStep < 4) {
            $this->currentStep++;
        }
    }

    public function previousStep(): void
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;
        }
    }

    public function resetWizard(): void
    {
        $this->currentStep = 1;
        $this->provider = null;
        $this->verificationCode = '';
        $this->qrCodeUrl = '';
        $this->secret = '';
        $this->recoveryCodes = [];
        $this->isEmailCodeVerified = false;
        $this->isAuthenticatorVerified = false;
        $this->lastAuthenticatorAttempt = null;
        $this->clearEmailVerificationState();
        $this->resetErrorBag('verificationCode');
    }

    public function render(): View
    {
        return view('livewire.actions.setup-two-factor-wizzard');
    }

    private function verifyEmailCode(): void
    {
        $this->verificationCode = mb_trim($this->verificationCode);

        if ($this->verificationCode === '') {
            $this->addError('verificationCode', __('auth.two_factor.email.messages.code_invalid'));

            return;
        }

        if (! preg_match('/^\d{6}$/', $this->verificationCode)) {
            $this->addError('verificationCode', __('auth.two_factor.email.messages.code_invalid'));

            return;
        }

        $cachedCode = Cache::get($this->emailCodeCacheKey());

        if ($cachedCode === null) {
            $this->addError('verificationCode', __('auth.two_factor.email.messages.code_expired'));

            return;
        }

        $attempts = (int) Cache::get($this->emailAttemptsCacheKey(), 0);

        if ($attempts >= self::EMAIL_CODE_MAX_ATTEMPTS) {
            $this->clearEmailVerificationState();
            $this->addError('verificationCode', __('auth.two_factor.email.messages.code_attempts_exceeded'));

            return;
        }

        if (! hash_equals($cachedCode, $this->verificationCode)) {
            Cache::put(
                key: $this->emailAttemptsCacheKey(),
                value: $attempts + 1,
                ttl: now()->addMinutes(self::EMAIL_CODE_TTL_MINUTES)
            );

            $this->addError('verificationCode', __('auth.two_factor.email.messages.code_invalid'));

            return;
        }

        $this->isEmailCodeVerified = true;
        $this->generateRecoveryCodes();

        $this->clearEmailVerificationState();
        $this->nextStep();
    }

    private function emailCodeCacheKey(): string
    {
        return sprintf('two_factor:email:code:%s', $this->user->getKey());
    }

    private function emailAttemptsCacheKey(): string
    {
        return sprintf('two_factor:email:attempts:%s', $this->user->getKey());
    }

    private function clearEmailVerificationState(): void
    {
        if (! isset($this->user)) {
            return;
        }

        Cache::forget($this->emailCodeCacheKey());
        Cache::forget($this->emailAttemptsCacheKey());
    }

    private function persistTwoFactorSettings(): void
    {
        $securitySetting = $this->user->settings()
            ->where('key', UserSettings::SECURITY)
            ->first();

        if ($securitySetting === null) {
            throw ValidationException::withMessages([
                'two_factor' => __('auth.two_factor.email.messages.missing_settings'),
            ]);
        }

        $securitySetting->updateValueAttribute('two_factor', [
            'provider' => $this->provider,
            'status' => TwoFactor::ENABLED->value,
            'secret' => $this->provider === TwoFactorProvider::AUTHENTICATOR->value ? $this->secret : null,
            'confirmed_at' => now()->toDateTimeString(),
            'recovery_codes' => $this->recoveryCodes,
        ]);

        Cache::forget($this->emailCodeCacheKey());
        Cache::forget($this->emailAttemptsCacheKey());

        $this->user->refresh();
    }

    private function verifyAuthenticatorCode(): void
    {
        $this->resetErrorBag('verificationCode');
        $this->verificationCode = mb_trim($this->verificationCode);

        if (! preg_match('/^\d{6}$/', $this->verificationCode)) {
            $this->addError('verificationCode', __('auth.two_factor.messages.code_invalid'));

            return;
        }

        if ($this->lastAuthenticatorAttempt !== null && now()->subSeconds(15)->lessThan($this->lastAuthenticatorAttempt)) {
            $this->addError('verificationCode', __('auth.two_factor.messages.code_recent'));

            return;
        }

        $google2FA = new Google2FA();

        try {
            if (! $google2FA->verifyKey($this->secret, $this->verificationCode)) {
                $this->addError('verificationCode', __('auth.two_factor.messages.code_invalid'));
                $this->lastAuthenticatorAttempt = CarbonImmutable::now();

                return;
            }
        } catch (IncompatibleWithGoogleAuthenticatorException|SecretKeyTooShortException|InvalidCharactersException $exception) {
            report($exception);
            $this->addError('verificationCode', __('auth.two_factor.messages.code_invalid'));
            $this->lastAuthenticatorAttempt = CarbonImmutable::now();

            return;
        }

        $this->isAuthenticatorVerified = true;
        $this->generateRecoveryCodes();
        $this->nextStep();
    }
}
