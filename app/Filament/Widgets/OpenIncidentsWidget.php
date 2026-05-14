<?php

namespace App\Filament\Widgets;

use App\Services\ApiClient;
use Filament\Notifications\Notification;
use Filament\Widgets\Widget;

class OpenIncidentsWidget extends Widget
{
    protected static ?string $heading = 'Incidentes Abertos';
    protected static ?int $sort = 3;
    protected static ?string $pollingInterval = '30s';
    protected static bool $isLazy = true;
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

    public function investigate(int $id): void
    {
        $result = rescue(
            fn () => app(ApiClient::class)->updateIncident($id, ['status' => 'investigating']),
            ['success' => false]
        );

        if ($result['success'] ?? false) {
            Notification::make()->title('Incidente em investigação')->warning()->send();
        } else {
            Notification::make()->title('Erro ao atualizar incidente')->danger()->send();
        }

        $this->incidents = rescue(fn () => app(ApiClient::class)->openIncidents(), []);
    }

    public function resolve(int $id): void
    {
        $result = rescue(
            fn () => app(ApiClient::class)->updateIncident($id, ['status' => 'resolved']),
            ['success' => false]
        );

        if ($result['success'] ?? false) {
            Notification::make()->title('Incidente resolvido')->success()->send();
        } else {
            Notification::make()->title('Erro ao resolver incidente')->danger()->send();
        }

        $this->incidents = rescue(fn () => app(ApiClient::class)->openIncidents(), []);
    }
}
