<?php

namespace App\Livewire\Profile\TwoFactor;

use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Enums\Settings\TwoFactor;
use App\Enums\Settings\UserSettings;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Carbon\Carbon;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Str;
use Livewire\Component;
use Masmerise\Toaster\Toastable;
use PragmaRX\Google2FA\Exceptions\IncompatibleWithGoogleAuthenticatorException;
use PragmaRX\Google2FA\Exceptions\InvalidCharactersException;
use PragmaRX\Google2FA\Exceptions\SecretKeyTooShortException;
use PragmaRX\Google2FA\Google2FA;

class EnableTwoFactor extends Component
{
    use Toastable;

    public string $secret;

    public string $qrCode;

    public string $code = '';

    public function mount(?Authenticatable $user = null): void
    {
        $g2fa = new Google2FA();

        $this->secret = $g2fa->generateSecretKey();

        $qrUrl = $g2fa->getQRCodeUrl(
            company: config('app.name'),
            holder: $user->email,
            secret: $this->secret
        );

        $qrWriterBackend = new Writer(
            renderer: new ImageRenderer(
                rendererStyle: new RendererStyle(size: 250, margin: 1),
                imageBackEnd: new SvgImageBackEnd()
            )
        );

        $this->qrCode = $qrWriterBackend->writeString($qrUrl);

        $pattern = '/(<svg\b[^>]*)(>)/i';
        $replacement = '$1 class="mx-auto rounded-lg"$2';
        $this->qrCode = preg_replace($pattern, $replacement, $this->qrCode);
    }

    public function enable(): ?StreamedResponse
    {
        $g2fa = new Google2FA();
        $user = auth()->user();

        $backupCodes = $this->generateRecoveryCodes(count: 10);

        try {
            if ($g2fa->verifyKey(secret: $this->secret, key: $this->code)) {
                $user->settings->where('key', UserSettings::SECURITY)->first()->updateValueAttribute(
                    path: 'two_factor',
                    newValue: [
                        'status' => TwoFactor::ENABLED->value,
                        'secret' => $this->secret,
                        'confirmed_at' => Carbon::now(),
                        'recovery_codes' => $backupCodes,
                    ]
                );

                $this->reset('code');
                $this->dispatch('close-modal');
                $this->dispatch('two-factor-status-updated');

                $this->success(
                    message: __('notifications.toasts.two_factor.enabled'),
                );

                return download_backup_codes(
                    filename: Str::slug(title: config('app.name') . ' Two Factor Backup Codes', separator: '_') . '.txt',
                    backupCodes: $backupCodes
                );
            }
        }
        catch (IncompatibleWithGoogleAuthenticatorException|SecretKeyTooShortException|InvalidCharactersException) {
            $this->error(
                message: __('notifications.toasts.two_factor.error'),
            );
        }

        return null;
    }

    private function generateRecoveryCodes(int $count = 8): array
    {
        return collect(range(1, $count))
            ->map(fn () => Str::upper(Str::random(4) . '-' . Str::random(4)))
            ->toArray();
    }

    public function render()
    {
        return view('livewire.profile.two-factor.enable-two-factor');
    }
}
