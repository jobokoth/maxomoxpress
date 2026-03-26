<?php

namespace App\Filament\PlatformAdmin\Resources\SchoolResource\Pages;

use App\Filament\PlatformAdmin\Resources\SchoolResource;
use Filament\Resources\Pages\ListRecords;

class ListSchools extends ListRecords
{
    protected static string $resource = SchoolResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
