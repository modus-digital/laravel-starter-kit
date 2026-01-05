<?php

declare(strict_types=1);

namespace App\Filament\Resources\Modules\Clients\Pages;

use App\Filament\Resources\Modules\Clients\ClientResource;
use App\Models\Modules\Clients\Client;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\Facades\Activity;

/**
 * @property Client $record
 */
final class EditClient extends EditRecord
{
    protected static string $resource = ClientResource::class;

    /** @var array<string, mixed> */
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
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        $record = $this->record;

        // Get the changes that were made
        $changes = $record->getChanges();

        // Log activity for each changed field
        foreach ($changes as $attribute => $newValue) {
            // Skip timestamps and other fields we don't want to log
            if (in_array($attribute, ['updated_at', 'created_at', 'deleted_at'])) {
                continue;
            }

            $oldValue = $this->originalValues[$attribute] ?? null;

            // Only log if there was an actual change
            if ($oldValue !== $newValue) {
                Activity::inLog('administration')
                    ->event('client.updated')
                    ->causedBy(Auth::user())
                    ->performedOn($record)
                    ->withProperties([
                        'client' => [
                            'id' => $record->id,
                            'name' => $record->name,
                        ],
                        'attribute' => $attribute,
                        'old' => $oldValue,
                        'new' => $newValue,
                    ])
                    ->log('');
            }
        }
    }
}
