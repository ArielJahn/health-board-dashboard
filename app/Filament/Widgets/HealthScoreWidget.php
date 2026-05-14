<?php

namespace App\Filament\Widgets;

use App\Services\ApiClient;
use Filament\Widgets\Widget;

class HealthScoreWidget extends Widget
{
    protected static ?string $heading = 'Health Score por Repositório';
    protected static ?int $sort = 2;
    protected static ?string $pollingInterval = '30s';
    protected static bool $isLazy = true;
    protected int|string|array $columnSpan = 'full';
    protected static string $view = 'filament.widgets.health-score';

    public array $scores = [];

    public function getHeading(): ?string
    {
        return static::$heading;
    }

    public function mount(): void
    {
        $scores = rescue(fn () => app(ApiClient::class)->healthScores(), []);

        $this->scores = collect($scores)
            ->map(fn ($score) => [
                'id'        => $score['repository_id'],
                'name'      => basename($score['repository']),
                'full_name' => $score['repository'],
                'score'     => $score['score'] ?? 0,
                'status'    => $score['status'] ?? 'unknown',
            ])
            ->sortBy('score')
            ->values()
            ->toArray();
    }
}
