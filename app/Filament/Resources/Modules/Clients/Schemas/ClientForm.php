<?php

declare(strict_types=1);

namespace App\Filament\Resources\Modules\Clients\Schemas;

use Filament\Schemas\Schema;

final class ClientForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                //
            ]);
    }
}
