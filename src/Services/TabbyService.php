<?php

namespace Aghfatehi\Tabby\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class TabbyService
{
    protected Client $client;

    protected array $config;

    public function __construct()
    {
        $this->config = config('tabby');
    }

    protected function client(): Client
    {
        if (!isset($this->client)) {
            $this->client = new Client([
                'base_uri' => $this->baseUrl(),
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->config['secret_key'],
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ],
                'timeout' => 30,
            ]);
        }

        return $this->client;
    }

    public function baseUrl(): string
    {
        if ($this->config['sandbox']) {
            return $this->config['sandbox_url'];
        }

        $region = $this->config['region'] ?? 'sa';

        return $this->config['api_urls'][$region] ?? 'https://api.tabby.ai';
    }

    public function createCheckout(array $data): array
    {
        return $this->post('/api/v2/checkout', $data);
    }

    public function getPayment(string $paymentId): array
    {
        return $this->get("/api/v2/payments/{$paymentId}");
    }

    public function updatePayment(string $paymentId, array $data): array
    {
        return $this->put("/api/v2/payments/{$paymentId}", $data);
    }

    public function capturePayment(string $paymentId, string $amount, string $referenceId = ''): array
    {
        return $this->post("/api/v2/payments/{$paymentId}/captures", [
            'amount' => $amount,
            'reference_id' => $referenceId ?: uniqid('capture_'),
        ]);
    }

    public function refundPayment(string $paymentId, string $amount, string $referenceId = ''): array
    {
        return $this->post("/api/v2/payments/{$paymentId}/refunds", [
            'amount' => $amount,
            'reference_id' => $referenceId ?: uniqid('refund_'),
        ]);
    }

    public function listPayments(array $filters = []): array
    {
        return $this->get('/api/v2/payments', $filters);
    }

    public function webhookRegister(string $url, array $header = []): array
    {
        $options = ['url' => $url];
        if (!empty($header)) {
            $options['header'] = $header;
        }

        return $this->post('/api/v1/webhooks', $options);
    }

    public function webhookList(): array
    {
        return $this->get('/api/v1/webhooks');
    }

    public function webhookGet(string $webhookId): array
    {
        return $this->get("/api/v1/webhooks/{$webhookId}");
    }

    public function webhookUpdate(string $webhookId, string $url): array
    {
        return $this->put("/api/v1/webhooks/{$webhookId}", ['url' => $url]);
    }

    public function webhookDelete(string $webhookId): array
    {
        return $this->delete("/api/v1/webhooks/{$webhookId}");
    }

    public function formatAmount(float $amount): string
    {
        return number_format($amount, 2, '.', '');
    }

    protected function get(string $path, array $query = []): array
    {
        try {
            $response = $this->client()->get($path, ['query' => $query]);
            $body = json_decode($response->getBody()->getContents(), true);

            $this->log('GET', $path, ['query' => $query], $body);

            return $body ?? [];
        } catch (\Throwable $e) {
            $this->logError('GET', $path, $e);
            throw $e;
        }
    }

    protected function post(string $path, array $body = []): array
    {
        try {
            $response = $this->client()->post($path, ['json' => $body]);
            $result = json_decode($response->getBody()->getContents(), true);

            $this->log('POST', $path, $body, $result);

            return $result ?? [];
        } catch (\Throwable $e) {
            $this->logError('POST', $path, $e);
            throw $e;
        }
    }

    protected function put(string $path, array $body = []): array
    {
        try {
            $response = $this->client()->put($path, ['json' => $body]);
            $result = json_decode($response->getBody()->getContents(), true);

            $this->log('PUT', $path, $body, $result);

            return $result ?? [];
        } catch (\Throwable $e) {
            $this->logError('PUT', $path, $e);
            throw $e;
        }
    }

    protected function delete(string $path): array
    {
        try {
            $response = $this->client()->delete($path);
            $result = json_decode($response->getBody()->getContents(), true);

            $this->log('DELETE', $path, [], $result);

            return $result ?? [];
        } catch (\Throwable $e) {
            $this->logError('DELETE', $path, $e);
            throw $e;
        }
    }

    protected function log(string $method, string $path, array $request, mixed $response): void
    {
        if ($this->config['logging'] ?? true) {
            Log::debug('Tabby API', [
                'method' => $method,
                'path' => $path,
                'request' => $request,
                'response' => $response,
            ]);
        }
    }

    protected function logError(string $method, string $path, \Throwable $e): void
    {
        Log::error("Tabby API {$method} Error", [
            'path' => $path,
            'message' => $e->getMessage(),
        ]);
    }
}
