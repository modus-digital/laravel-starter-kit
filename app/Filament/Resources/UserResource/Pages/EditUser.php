<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    // The resource class this page belongs to.
    protected static string $resource = UserResource::class;

    // The actions to display in the header.
    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    // Mutate the form data before saving.
    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (empty($data['password'])) {
            unset($data['password']);
        }

        return $data;
    }
}
