<?php

declare(strict_types=1);

namespace App\Filament\Resources\Core\Users\Pages;

use App\Enums\RBAC\Role;
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

        Activity::inLog('administration')
            ->event('user.updated')
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
