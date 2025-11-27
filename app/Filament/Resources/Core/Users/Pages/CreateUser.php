<?php

declare(strict_types=1);

namespace App\Filament\Resources\Core\Users\Pages;

use App\Enums\RBAC\Role;
use App\Filament\Resources\Core\Users\UserResource;
use App\Notifications\Auth\AccountCreated;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Spatie\Activitylog\Facades\Activity;

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
        /** @var \App\Models\User $record */
        $record = $this->record;

        if ($this->generatedPassword !== '' && $this->generatedPassword !== '0') {
            $record->notify(new AccountCreated(password: $this->generatedPassword));
        }

        Activity::inLog('administration')
            ->event('user.created')
            ->causedBy(Auth::user())
            ->performedOn($record)
            ->withProperties([
                'user' => [
                    'id' => $record->id,
                    'name' => $record->name,
                    'email' => $record->email,
                    'status' => $record->status->getLabel(),
                    'roles' => $record->roles->first()?->name
                        ? Role::from($record->roles->first()->name)->getLabel()
                        : null,
                ],
            ])
            ->log('');
    }
}
