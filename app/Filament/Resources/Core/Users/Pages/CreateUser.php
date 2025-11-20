<?php

declare(strict_types=1);

namespace App\Filament\Resources\Core\Users\Pages;

use App\Filament\Resources\Core\Users\UserResource;
use App\Notifications\Auth\AccountCreated;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

final class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    private string $generatedPassword = '';

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->generatedPassword = Str::random(length: 10);
        $data['password'] = $this->generatedPassword;

        return $data;
    }

    protected function afterCreate(): void
    {
        if ($this->generatedPassword && $this->record) {
            $this->record->notify(new AccountCreated(password: $this->generatedPassword));
        }
    }
}
