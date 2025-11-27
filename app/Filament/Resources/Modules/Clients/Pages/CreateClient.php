<?php

declare(strict_types=1);

namespace App\Filament\Resources\Modules\Clients\Pages;

use App\Filament\Resources\Modules\Clients\ClientResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\Facades\Activity;

final class CreateClient extends CreateRecord
{
    protected static string $resource = ClientResource::class;

    protected function afterCreate(): void
    {
        /** @var \App\Models\Modules\Clients\Client $record */
        $record = $this->record;

        Activity::inLog('administration')
            ->event('client.created')
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
