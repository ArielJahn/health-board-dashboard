<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class ApiClient
{
    private PendingRequest $http;
    private int $ttl = 60;

    public function __construct()
    {
        $client = Http::baseUrl(config('services.api.base_url'))
            ->withToken(config('services.api.token'))
            ->acceptJson()
            ->timeout(15);

        if (app()->environment('local')) {
            $client = $client->withoutVerifying();
        }

        $this->http = $client;
    }

    // ── Repositories ──────────────────────────────────────────────────────────

    public function repositories(): array
    {
        return Cache::remember('api.repositories', $this->ttl, fn () =>
            $this->http->get('/repositories')->json('data', [])
        );
    }

    public function repository(int $id): array
    {
        return Cache::remember("api.repository.{$id}", $this->ttl, fn () =>
            $this->http->get("/repositories/{$id}")->json('data', [])
        );
    }

    public function createRepository(array $data): array
    {
        $response = $this->http->post('/repositories', $data);
        Cache::flush();

        return [
            'success' => $response->successful(),
            'data'    => $response->json('data', []),
            'status'  => $response->status(),
            'errors'  => $response->json('errors', []),
        ];
    }

    public function deleteRepository(int $id): bool
    {
        $ok = $this->http->delete("/repositories/{$id}")->successful();
        Cache::flush();

        return $ok;
    }

    // ── Pipelines ─────────────────────────────────────────────────────────────

    /**
     * @param  array{status?: string}  $filters
     */
    public function pipelines(?int $repositoryId = null, array $filters = [], int $limit = 50): array
    {
        $key = 'api.pipelines.' . ($repositoryId ?? 'all') . '.' . md5(serialize($filters)) . ".{$limit}";

        return Cache::remember($key, $this->ttl, function () use ($repositoryId, $filters, $limit) {
            if ($repositoryId) {
                return $this->http
                    ->get("/repositories/{$repositoryId}/pipelines", array_merge($filters, ['limit' => $limit]))
                    ->json('data', []);
            }

            return $this->http->get('/pipelines', $filters)->json('data', []);
        });
    }

    public function createPipeline(array $data): array
    {
        $response = $this->http->post('/pipelines', $data);
        Cache::flush();

        return [
            'success' => $response->successful(),
            'data'    => $response->json('data', []),
            'errors'  => $response->json('errors', []),
        ];
    }

    // ── Releases ──────────────────────────────────────────────────────────────

    /**
     * @param  array{environment?: string}  $filters
     */
    public function releases(?int $repositoryId = null, array $filters = [], int $limit = 20): array
    {
        $key = 'api.releases.' . ($repositoryId ?? 'all') . '.' . md5(serialize($filters)) . ".{$limit}";

        return Cache::remember($key, $this->ttl, function () use ($repositoryId, $filters, $limit) {
            if ($repositoryId) {
                return $this->http
                    ->get("/repositories/{$repositoryId}/releases", array_merge($filters, ['limit' => $limit]))
                    ->json('data', []);
            }

            return $this->http->get('/releases', $filters)->json('data', []);
        });
    }

    public function createRelease(array $data): array
    {
        $response = $this->http->post('/releases', $data);
        Cache::flush();

        return [
            'success' => $response->successful(),
            'data'    => $response->json('data', []),
            'errors'  => $response->json('errors', []),
        ];
    }

    // ── Incidents ─────────────────────────────────────────────────────────────

    /**
     * @param  array{status?: string, severity?: string}  $filters
     */
    public function incidents(array $filters = []): array
    {
        $key = 'api.incidents.' . md5(serialize($filters));

        return Cache::remember($key, $this->ttl, fn () =>
            $this->http->get('/incidents', $filters)->json('data', [])
        );
    }

    public function openIncidents(array $filters = []): array
    {
        $key = 'api.incidents.open.' . md5(serialize($filters));

        return Cache::remember($key, $this->ttl, fn () =>
            $this->http->get('/incidents/open', $filters)->json('data', [])
        );
    }

    public function createIncident(array $data): array
    {
        $response = $this->http->post('/incidents', $data);
        Cache::flush();

        return [
            'success' => $response->successful(),
            'data'    => $response->json('data', []),
            'errors'  => $response->json('errors', []),
        ];
    }

    public function updateIncident(int $id, array $data): array
    {
        $response = $this->http->put("/incidents/{$id}", $data);
        Cache::flush();

        return [
            'success' => $response->successful(),
            'data'    => $response->json('data', []),
            'errors'  => $response->json('errors', []),
        ];
    }

    // ── Health Score ──────────────────────────────────────────────────────────

    public function healthScores(): array
    {
        return Cache::remember('api.health_scores', $this->ttl, fn () =>
            $this->http->get('/health-scores')->json('data', [])
        );
    }

    public function healthScore(int $repositoryId): array
    {
        return Cache::remember("api.health_score.{$repositoryId}", $this->ttl, fn () =>
            $this->http->get("/health-score/{$repositoryId}")->json('data', [])
        );
    }
}
