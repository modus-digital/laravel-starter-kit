<?php

declare(strict_types=1);

namespace App\Filament\Resources\Core\Users\Pages;

use App\Filament\Resources\Core\Users\UserResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\Facades\Activity;

final class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

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
        /** @var \App\Models\User $record */
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
                    ->event('user.updated')
                    ->causedBy(Auth::user())
                    ->performedOn($record)
                    ->withProperties([
                        'user' => [
                            'id' => $record->id,
                            'name' => $record->name,
                            'email' => $record->email,
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
