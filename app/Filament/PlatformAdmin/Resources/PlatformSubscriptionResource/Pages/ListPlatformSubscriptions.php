<?php

namespace App\Filament\PlatformAdmin\Resources\PlatformSubscriptionResource\Pages;

use App\Filament\PlatformAdmin\Resources\PlatformSubscriptionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPlatformSubscriptions extends ListRecords
{
    protected static string $resource = PlatformSubscriptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
