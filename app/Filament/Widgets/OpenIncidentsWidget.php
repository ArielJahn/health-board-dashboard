<?php

namespace App\Filament\Widgets;

use App\Services\ApiClient;
use Filament\Widgets\Widget;

class OpenIncidentsWidget extends Widget
{
    protected static ?string $heading = 'Incidentes Abertos';
    protected static ?int $sort = 3;
    protected int|string|array $columnSpan = 'full';
    protected static string $view = 'filament.widgets.open-incidents';

    public array $incidents = [];

    public function getHeading(): ?string
    {
        return static::$heading;
    }

    public function mount(): void
    {
        $this->incidents = rescue(fn () => app(ApiClient::class)->openIncidents(), []);
    }
}
