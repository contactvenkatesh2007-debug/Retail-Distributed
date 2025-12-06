<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class ServiceClient
{
    protected string $baseUrl;
    protected int $timeout;

    public function __construct(string $serviceName)
    {
        $config = config("services.microservices.{$serviceName}", []);
        $this->baseUrl = $config['base_url'] ?? '';
        $this->timeout = $config['timeout'] ?? 30;
    }

    /**
     * Make HTTP GET request
     */
    public function get(string $endpoint, array $params = []): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->get("{$this->baseUrl}/{$endpoint}", $params);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                    'status' => $response->status(),
                ];
            }

            return [
                'success' => false,
                'error' => $response->json()['message'] ?? 'Request failed',
                'status' => $response->status(),
            ];
        } catch (Exception $e) {
            Log::error("Service request failed: {$e->getMessage()}", [
                'service' => $this->baseUrl,
                'endpoint' => $endpoint,
            ]);

            return [
                'success' => false,
                'error' => 'Service unavailable',
                'status' => 503,
            ];
        }
    }

    /**
     * Make HTTP POST request
     */
    public function post(string $endpoint, array $data = []): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->post("{$this->baseUrl}/{$endpoint}", $data);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                    'status' => $response->status(),
                ];
            }

            return [
                'success' => false,
                'error' => $response->json()['message'] ?? 'Request failed',
                'status' => $response->status(),
            ];
        } catch (Exception $e) {
            Log::error("Service request failed: {$e->getMessage()}", [
                'service' => $this->baseUrl,
                'endpoint' => $endpoint,
            ]);

            return [
                'success' => false,
                'error' => 'Service unavailable',
                'status' => 503,
            ];
        }
    }

    /**
     * Make HTTP PUT request
     */
    public function put(string $endpoint, array $data = []): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->put("{$this->baseUrl}/{$endpoint}", $data);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                    'status' => $response->status(),
                ];
            }

            return [
                'success' => false,
                'error' => $response->json()['message'] ?? 'Request failed',
                'status' => $response->status(),
            ];
        } catch (Exception $e) {
            Log::error("Service request failed: {$e->getMessage()}", [
                'service' => $this->baseUrl,
                'endpoint' => $endpoint,
            ]);

            return [
                'success' => false,
                'error' => 'Service unavailable',
                'status' => 503,
            ];
        }
    }

    /**
     * Make HTTP DELETE request
     */
    public function delete(string $endpoint): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->delete("{$this->baseUrl}/{$endpoint}");

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                    'status' => $response->status(),
                ];
            }

            return [
                'success' => false,
                'error' => $response->json()['message'] ?? 'Request failed',
                'status' => $response->status(),
            ];
        } catch (Exception $e) {
            Log::error("Service request failed: {$e->getMessage()}", [
                'service' => $this->baseUrl,
                'endpoint' => $endpoint,
            ]);

            return [
                'success' => false,
                'error' => 'Service unavailable',
                'status' => 503,
            ];
        }
    }
}

