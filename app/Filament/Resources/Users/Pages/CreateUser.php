<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Notifications\Auth\AccountCreated;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    private string $generatedPassword = '';

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->generatedPassword = Str::random(18);

        $data['password'] = $this->generatedPassword;

        return $data;
    }

    protected function afterCreate(): void
    {
        if ($this->generatedPassword && $this->record) {
            $this->record->notify(new AccountCreated($this->generatedPassword));
        }
    }
}
