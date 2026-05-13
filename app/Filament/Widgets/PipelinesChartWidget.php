<?php

namespace App\Filament\Widgets;

use App\Services\ApiClient;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class PipelinesChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Pipelines nos últimos 30 dias';
    protected static ?int $sort = 4;
    protected int|string|array $columnSpan = 'full';

    protected function getData(): array
    {
        $pipelines = rescue(fn () => app(ApiClient::class)->pipelines(), []);

        $cutoff = now()->subDays(30);

        $byDay = collect($pipelines)
            ->filter(fn ($p) => isset($p['run_at']) && Carbon::parse($p['run_at'])->gte($cutoff))
            ->groupBy(fn ($p) => Carbon::parse($p['run_at'])->format('d/m'))
            ->sortKeys();

        $labels = $byDay->keys()->toArray();

        $success = $byDay->map(fn ($group) =>
            $group->filter(fn ($p) => ($p['status'] ?? '') === 'success')->count()
        )->values()->toArray();

        $failure = $byDay->map(fn ($group) =>
            $group->filter(fn ($p) => ($p['status'] ?? '') === 'failure')->count()
        )->values()->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Sucesso',
                    'data' => $success,
                    'borderColor' => '#22c55e',
                    'backgroundColor' => 'rgba(34,197,94,0.15)',
                    'fill' => true,
                    'tension' => 0.4,
                ],
                [
                    'label' => 'Falha',
                    'data' => $failure,
                    'borderColor' => '#ef4444',
                    'backgroundColor' => 'rgba(239,68,68,0.15)',
                    'fill' => true,
                    'tension' => 0.4,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => ['position' => 'bottom'],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => ['stepSize' => 1],
                ],
            ],
        ];
    }
}
