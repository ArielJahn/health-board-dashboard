<?php

namespace App\Filament\Resources\DashboardConfigResource\Pages;

use App\Filament\Resources\DashboardConfigResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDashboardConfig extends EditRecord
{
    protected static string $resource = DashboardConfigResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
