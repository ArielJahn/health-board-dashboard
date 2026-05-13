<?php

namespace App\Filament\Widgets;

use App\Services\ApiClient;
use Filament\Widgets\ChartWidget;

class CiHistogramWidget extends ChartWidget
{
    protected static ?string $heading = 'Taxa de sucesso CI por repositório';
    protected static ?int $sort = 5;
    protected int|string|array $columnSpan = 'full';

    protected function getData(): array
    {
        $api = app(ApiClient::class);
        $repositories = rescue(fn () => $api->repositories(), []);

        $labels = [];
        $successRates = [];
        $colors = [];

        foreach ($repositories as $repo) {
            $pipelines = rescue(fn () => $api->pipelines($repo['id']), []);

            $total = count($pipelines);
            $success = count(array_filter($pipelines, fn ($p) => ($p['status'] ?? '') === 'success'));
            $rate = $total > 0 ? round(($success / $total) * 100) : 0;

            $labels[] = $repo['name'];
            $successRates[] = $rate;
            $colors[] = match(true) {
                $rate >= 80 => 'rgba(34,197,94,0.8)',
                $rate >= 50 => 'rgba(234,179,8,0.8)',
                default     => 'rgba(239,68,68,0.8)',
            };
        }

        return [
            'datasets' => [
                [
                    'label' => 'Taxa de sucesso (%)',
                    'data' => $successRates,
                    'backgroundColor' => $colors,
                    'borderRadius' => 6,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => ['display' => false],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'max' => 100,
                    'ticks' => [
                        'callback' => "function(v){ return v + '%' }",
                    ],
                ],
            ],
        ];
    }
}
