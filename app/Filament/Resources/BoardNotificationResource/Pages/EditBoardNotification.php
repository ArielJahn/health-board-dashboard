<?php

namespace App\Filament\Resources\BoardNotificationResource\Pages;

use App\Filament\Resources\BoardNotificationResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditBoardNotification extends EditRecord
{
    protected static string $resource = BoardNotificationResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
