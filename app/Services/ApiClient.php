<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class ApiClient
{
    private PendingRequest $http;

    public function __construct()
    {
        $this->http = Http::baseUrl(config('services.api.base_url'))
            ->withToken(config('services.api.token'))
            ->acceptJson()
            ->timeout(15);
    }

    public function repositories(): array
    {
        return $this->http->get('/repositories')->json('data', []);
    }

    public function repository(int $id): array
    {
        return $this->http->get("/repositories/{$id}")->json('data', []);
    }

    public function pipelines(?int $repositoryId = null): array
    {
        $endpoint = $repositoryId ? "/repositories/{$repositoryId}/pipelines" : '/pipelines';

        return $this->http->get($endpoint)->json('data', []);
    }

    public function releases(?int $repositoryId = null): array
    {
        $endpoint = $repositoryId ? "/repositories/{$repositoryId}/releases" : '/releases';

        return $this->http->get($endpoint)->json('data', []);
    }

    public function incidents(): array
    {
        return $this->http->get('/incidents')->json('data', []);
    }

    public function openIncidents(): array
    {
        return $this->http->get('/incidents/open')->json('data', []);
    }

    public function healthScore(int $repositoryId): array
    {
        return $this->http->get("/health-score/{$repositoryId}")->json('data', []);
    }
}
