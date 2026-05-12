<?php

namespace App\Filament\Resources\DashboardConfigResource\Pages;

use App\Filament\Resources\DashboardConfigResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDashboardConfigs extends ListRecords
{
    protected static string $resource = DashboardConfigResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
