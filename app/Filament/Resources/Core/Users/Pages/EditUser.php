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
        Activity::inLog('administration')
            ->event('user.updated')
            ->causedBy(Auth::user())
            ->performedOn($this->record)
            ->withProperties([
                'user' => [
                    'id' => $this->record->id,
                    'name' => $this->record->name,
                    'email' => $this->record->email,
                    'status' => $this->record->status->getLabel(),
                    'roles' => Role::from($this->record->roles->first()->name)->getLabel(),
                ]
            ])
            ->log('');
    }
}
