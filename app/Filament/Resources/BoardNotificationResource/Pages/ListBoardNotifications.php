<?php

namespace App\Filament\Resources\BoardNotificationResource\Pages;

use App\Filament\Resources\BoardNotificationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBoardNotifications extends ListRecords
{
    protected static string $resource = BoardNotificationResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
