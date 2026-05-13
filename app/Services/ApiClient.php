<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class ApiClient
{
    private PendingRequest $http;

    public function __construct()
    {
        $client = Http::baseUrl(config('services.api.base_url'))
            ->withToken(config('services.api.token'))
            ->acceptJson()
            ->timeout(15);

        // Herd uses a local CA; skip verification in local dev
        if (app()->environment('local')) {
            $client = $client->withoutVerifying();
        }

        $this->http = $client;
    }

    public function repositories(): array
    {
        // Returns paginated: {"data":[...], "current_page":1, ...}
        return $this->http->get('/repositories')->json('data', []);
    }

    public function repository(int $id): array
    {
        // Returns single object: {"id":1, "name":"...", ...}
        return $this->http->get("/repositories/{$id}")->json() ?? [];
    }

    public function pipelines(?int $repositoryId = null): array
    {
        if ($repositoryId) {
            // Returns plain array: [{...}, {...}]
            return $this->http->get("/repositories/{$repositoryId}/pipelines")->json() ?? [];
        }

        // Returns paginated: {"data":[...]}
        return $this->http->get('/pipelines')->json('data', []);
    }

    public function releases(?int $repositoryId = null): array
    {
        if ($repositoryId) {
            // Returns plain array (consistent with pipelines sub-resource)
            return $this->http->get("/repositories/{$repositoryId}/releases")->json() ?? [];
        }

        // Returns paginated: {"data":[...]}
        return $this->http->get('/releases')->json('data', []);
    }

    public function incidents(): array
    {
        // Returns paginated: {"data":[...]}
        return $this->http->get('/incidents')->json('data', []);
    }

    public function openIncidents(): array
    {
        // Returns plain array: [{...}] or []
        return $this->http->get('/incidents/open')->json() ?? [];
    }

    public function healthScore(int $repositoryId): array
    {
        // Returns direct object: {"score":70, "status":"degraded", ...}
        return $this->http->get("/health-score/{$repositoryId}")->json() ?? [];
    }
}
