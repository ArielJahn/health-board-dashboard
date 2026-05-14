<?php

namespace App\Filament\Widgets;

use App\Services\ApiClient;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;
    protected static ?string $pollingInterval = '30s';
    protected static bool $isLazy = true;
    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $api = app(ApiClient::class);

        $repositories  = rescue(fn () => $api->repositories(), []);
        $pipelines     = rescue(fn () => $api->pipelines(), []);
        $openIncidents = rescue(fn () => $api->openIncidents(), []);
        $criticalOnly  = rescue(fn () => $api->openIncidents(['severity' => 'critical']), []);
        $releases      = rescue(fn () => $api->releases(), []);

        $successCount   = count(array_filter($pipelines, fn ($p) => ($p['status'] ?? '') === 'success'));
        $totalPipelines = count($pipelines);
        $successRate    = $totalPipelines > 0
            ? round(($successCount / $totalPipelines) * 100)
            : 0;

        $criticalIncidents = count($criticalOnly);

        return [
            Stat::make('Repositórios monitorados', count($repositories))
                ->description('Conectados à API')
                ->icon('heroicon-m-server')
                ->color('primary'),

            Stat::make('Taxa de sucesso CI', "{$successRate}%")
                ->description("{$successCount} de {$totalPipelines} pipelines")
                ->icon('heroicon-m-check-circle')
                ->color($successRate >= 80 ? 'success' : ($successRate >= 50 ? 'warning' : 'danger')),

            Stat::make('Incidentes abertos', count($openIncidents))
                ->description($criticalIncidents > 0 ? "{$criticalIncidents} crítico(s)" : 'Nenhum crítico')
                ->icon('heroicon-m-exclamation-triangle')
                ->color(count($openIncidents) === 0 ? 'success' : ($criticalIncidents > 0 ? 'danger' : 'warning')),

            Stat::make('Releases', count($releases))
                ->description('Total de deploys registrados')
                ->icon('heroicon-m-rocket-launch')
                ->color('info'),
        ];
    }
}
