<?php

namespace App\Filament\PlatformAdmin\Resources\SchoolResource\Pages;

use App\Filament\PlatformAdmin\Resources\SchoolResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSchool extends EditRecord
{
    protected static string $resource = SchoolResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
