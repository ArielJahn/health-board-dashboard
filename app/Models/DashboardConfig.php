<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DashboardConfig extends Model
{
    protected $fillable = [
        'user_id',
        'default_view',
        'refresh_interval',
        'theme',
        'pinned_repos',
    ];

    protected function casts(): array
    {
        return [
            'pinned_repos' => 'array',
            'refresh_interval' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
