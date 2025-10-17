<?php

declare(strict_types=1);

namespace ModusDigital\Clients\Filament\Resources\ClientResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use ModusDigital\Clients\Filament\Resources\ClientResource\ClientResource;

final class CreateClient extends CreateRecord
{
    protected static string $resource = ClientResource::class;
}
