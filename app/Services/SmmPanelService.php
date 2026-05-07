<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmmPanelService
{
    protected string $endpoint;
    protected string $apiId;
    protected string $apiKey;

    public function __construct()
    {
        $this->endpoint = rtrim(config('services.smmpanel.endpoint', ''), '/');
        $this->apiId = config('services.smmpanel.api_id', '');
        $this->apiKey = config('services.smmpanel.api_key', '');
    }

    /**
     * Check account balance.
     *
     * @return array|null
     */
    public function balance(): ?array
    {
        return $this->post('/api/balance');
    }

    /**
     * Get list of available services.
     *
     * @return array|null
     */
    public function services(): ?array
    {
        return $this->post('/api/services');
    }

    /**
     * Create a new order.
     *
     * @param int    $service  Service ID
     * @param string $target   Target (username, link, etc.)
     * @param int    $quantity Quantity
     * @return array|null
     */
    public function order(int $service, string $target, int $quantity): ?array
    {
        return $this->post('/api/order', [
            'service' => $service,
            'target' => $target,
            'quantity' => $quantity,
        ]);
    }

    /**
     * Check order status.
     *
     * @param int $orderId
     * @return array|null
     */
    public function status(int $orderId): ?array
    {
        return $this->post('/api/status', [
            'id' => $orderId,
        ]);
    }

    /**
     * Request a refill for an order.
     *
     * @param int $orderId
     * @return array|null
     */
    public function refill(int $orderId): ?array
    {
        return $this->post('/api/refill', [
            'id' => $orderId,
        ]);
    }

    /**
     * Check refill status.
     *
     * @param int $refillId
     * @return array|null
     */
    public function refillStatus(int $refillId): ?array
    {
        return $this->post('/api/refill/status', [
            'id' => $refillId,
        ]);
    }

    /**
     * Send POST request to SMM Panel API.
     *
     * @param string $path
     * @param array  $data
     * @return array|null
     */
    private function post(string $path, array $data = []): ?array
    {
        try {
            $response = Http::asForm()->post($this->endpoint . $path, array_merge([
                'api_id' => $this->apiId,
                'api_key' => $this->apiKey,
            ], $data));

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('SmmPanel request failed', [
                'path' => $path,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('SmmPanel request exception', [
                'path' => $path,
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }
}
