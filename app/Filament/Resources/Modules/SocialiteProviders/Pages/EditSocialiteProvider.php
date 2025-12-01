<?php

declare(strict_types=1);

namespace App\Filament\Resources\Modules\SocialiteProviders\Pages;

use App\Filament\Resources\Modules\SocialiteProviders\SocialiteProviderResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\Facades\Activity as ActivityFacade;

final class EditSocialiteProvider extends EditRecord
{
    protected static string $resource = SocialiteProviderResource::class;

    private array $originalValues = [];

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Store the original values before saving
        $this->originalValues = $this->record->getOriginal();

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        /** @var \App\Models\Modules\SocialiteProvider $record */
        $record = $this->record;

        // Get the changes that were made
        $changes = $record->getChanges();

        // Log activity for socialite provider update (generic, without sensitive details)
        $hasCredentialChanges = false;
        foreach ($changes as $attribute => $newValue) {
            // Skip timestamps and other fields we don't want to log
            if (in_array($attribute, ['updated_at', 'created_at', 'deleted_at'])) {
                continue;
            }

            $oldValue = $this->originalValues[$attribute] ?? null;

            // Only log if there was an actual change
            if ($oldValue !== $newValue) {
                $hasCredentialChanges = true;
                break;
            }
        }

        // Log a single activity entry for credential updates
        if ($hasCredentialChanges) {
            ActivityFacade::inLog('administration')
                ->event('socialite_provider.updated')
                ->causedBy(Auth::user())
                ->performedOn($record)
                ->withProperties([
                    'target' => $record->name,
                    'socialite_provider' => [
                        'id' => $record->id,
                        'name' => $record->name,
                    ],
                ])
                ->log('');
        }
    }
}
