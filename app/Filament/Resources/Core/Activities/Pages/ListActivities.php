<?php

declare(strict_types=1);

namespace App\Filament\Resources\Core\Activities\Pages;

use App\Filament\Resources\Core\Activities\ActivityResource;
use Filament\Resources\Pages\ListRecords;

final class ListActivities extends ListRecords
{
    protected static string $resource = ActivityResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
