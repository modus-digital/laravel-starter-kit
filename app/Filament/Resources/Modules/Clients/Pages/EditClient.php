<?php

declare(strict_types=1);

namespace App\Filament\Resources\Modules\Clients\Pages;

use App\Filament\Resources\Modules\Clients\ClientResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\Facades\Activity;

final class EditClient extends EditRecord
{
    protected static string $resource = ClientResource::class;

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
        /** @var \App\Models\Modules\Clients\Client $record */
        $record = $this->record;

        Activity::inLog('administration')
            ->event('client.updated')
            ->causedBy(Auth::user())
            ->performedOn($record)
            ->withProperties([
                'client' => [
                    'id' => $record->id,
                    'name' => $record->name,
                ],
            ])
            ->log('');
    }
}
