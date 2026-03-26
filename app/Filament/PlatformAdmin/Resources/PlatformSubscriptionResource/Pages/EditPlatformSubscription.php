<?php

namespace App\Filament\PlatformAdmin\Resources\PlatformSubscriptionResource\Pages;

use App\Filament\PlatformAdmin\Resources\PlatformSubscriptionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPlatformSubscription extends EditRecord
{
    protected static string $resource = PlatformSubscriptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
