<?php

namespace App\Filament\Widgets;

use App\Services\ApiClient;
use Filament\Widgets\Widget;

class HealthScoreWidget extends Widget
{
    protected static ?string $heading = 'Health Score por Repositório';
    protected static ?int $sort = 2;
    protected int|string|array $columnSpan = 'full';
    protected static string $view = 'filament.widgets.health-score';

    public array $scores = [];

    public function getHeading(): ?string
    {
        return static::$heading;
    }

    public function mount(): void
    {
        $api = app(ApiClient::class);
        $repositories = rescue(fn () => $api->repositories(), []);

        $this->scores = collect($repositories)
            ->map(function ($repo) use ($api) {
                $score = rescue(fn () => $api->healthScore($repo['id']), []);

                return [
                    'id' => $repo['id'],
                    'name' => $repo['name'],
                    'full_name' => $repo['full_name'],
                    'score' => $score['score'] ?? 0,
                    'status' => $score['status'] ?? 'unknown',
                ];
            })
            ->sortBy('score')
            ->values()
            ->toArray();
    }
}
