<?php

namespace Aghfatehi\Tabby\Services;

use Illuminate\Support\Facades\Log;

class TabbyService
{
    protected array $config;

    public function __construct()
    {
        $this->config = config('tabby');
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
        $url = $this->baseUrl() . $path;
        if (!empty($query)) {
            $url .= '?' . http_build_query($query);
        }

        return $this->makeApiRequest('GET', $url, null);
    }

    protected function post(string $path, array $body = []): array
    {
        $url = $this->baseUrl() . $path;

        return $this->makeApiRequest('POST', $url, json_encode($body));
    }

    protected function put(string $path, array $body = []): array
    {
        $url = $this->baseUrl() . $path;

        return $this->makeApiRequest('PUT', $url, json_encode($body));
    }

    protected function delete(string $path): array
    {
        $url = $this->baseUrl() . $path;

        return $this->makeApiRequest('DELETE', $url);
    }

    private function makeApiRequest($method, $endpoint, $body = null)
    {
        $url = $this->getBaseUrl() . $endpoint;

        $headers = [
            'Authorization: Bearer ' . $this->config['secret_key'],
            'Content-Type: application/json',
            'Accept: application/json',
        ];

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headers,
        ]);

        if ($body) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, $body);
        }

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error = curl_error($curl);

        curl_close($curl);

        if ($error) {
            Log::error('Tabby API Error:', ['error' => $error, 'url' => $url]);
            throw new \Exception($error);
        }

        $decoded = json_decode($response, true);

        // Log for debugging
        if (config('tabby.logging', true)) {
            Log::debug(
                "Tabby API Response:\n" . json_encode([
                    'method' => $method,
                    'endpoint' => $endpoint,
                    'http_code' => $httpCode,
                    'response' => $decoded
                ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
            );
        }

        return $decoded ?? [];
    }

    private function getBaseUrl()
    {
        $sandbox = config('tabby.sandbox', true);
        if ($sandbox) {
            return config('tabby.sandbox_url', 'https://api.tabby.ai');
        }

        $region = config('tabby.region', 'sa');
        return config("tabby.api_urls.{$region}", 'https://api.tabby.ai');
    }
}
