<?php

declare(strict_types=1);

namespace ModusDigital\Clients\Filament\Resources\ClientResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use ModusDigital\Clients\Filament\Resources\ClientResource\ClientResource;

final class ListClients extends ListRecords
{
    protected static string $resource = ClientResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
