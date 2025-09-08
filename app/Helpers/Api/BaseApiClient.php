<?php

namespace App\Helpers\Api;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Abstract base class for API clients
 */
abstract class BaseApiClient
{
    protected string $baseUrl;
    protected array $defaultHeaders = [];
    protected int $rateLimitDelay = 1; // seconds
    protected int $maxRetries = 3;

    public function __construct()
    {
        $this->baseUrl = $this->getBaseUrl();
        $this->defaultHeaders = $this->getDefaultHeaders();
    }

    abstract protected function getBaseUrl(): string;
    
    protected function getDefaultHeaders(): array
    {
        return [
            'Accept' => 'application/json',
            'User-Agent' => 'Laravel-Anime-App/1.0'
        ];
    }

    /**
     * Make API request with error handling and retry logic
     */
    protected function makeRequest(string $endpoint, array $params = [], string $method = 'GET'): array
    {
        $url = $this->baseUrl . '/' . ltrim($endpoint, '/');
        
        for ($attempt = 1; $attempt <= $this->maxRetries; $attempt++) {
            try {
                $http = Http::withHeaders($this->defaultHeaders)->timeout(30);
                
                if ($method === 'GET') {
                    $response = $http->get($url, $params);
                } elseif ($method === 'POST') {
                    $response = $http->post($url, $params);
                } else {
                    throw new \Exception("Unsupported HTTP method: {$method}");
                }

                if ($response->successful()) {
                    $this->handleRateLimit();
                    return $response->json();
                }

                if ($response->status() === 429) {
                    $this->handleRateLimit($attempt * 2); // Exponential backoff
                    continue;
                }

                throw new \Exception("API request failed with status: " . $response->status());

            } catch (\Exception $e) {
                Log::warning("API request attempt {$attempt} failed", [
                    'url' => $url,
                    'error' => $e->getMessage(),
                ]);

                if ($attempt === $this->maxRetries) {
                    throw new \Exception("API request failed after {$this->maxRetries} attempts: " . $e->getMessage());
                }

                sleep($attempt); // Progressive delay
            }
        }

        throw new \Exception('Unexpected error in API request');
    }

    /**
     * Handle rate limiting
     */
    protected function handleRateLimit(int $customDelay = null): void
    {
        $delay = $customDelay ?? $this->rateLimitDelay;
        if ($delay > 0) {
            sleep($delay);
        }
    }

    /**
     * Extract data from API response
     */
    protected function extractData(array $response, string $key = 'data'): array
    {
        return $response[$key] ?? [];
    }

    /**
     * Extract pagination info from API response
     */
    protected function extractPagination(array $response): array
    {
        return $response['pagination'] ?? [];
    }
}
