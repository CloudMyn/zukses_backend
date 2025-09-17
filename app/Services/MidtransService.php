<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class MidtransService
{
    protected string $serverKey;
    protected ?string $clientKey;
    protected bool $isProduction;
    protected string $baseUrl;
    protected int $timeoutSeconds = 10;

    public function __construct()
    {
        $this->serverKey = config('midtrans.server_key') ?? env('MIDTRANS_SERVER_KEY', '');
        $this->clientKey = config('midtrans.client_key') ?? env('MIDTRANS_CLIENT_KEY', null);
        $this->isProduction = (bool)(config('midtrans.is_production') ?? env('MIDTRANS_IS_PRODUCTION', false));

        $this->baseUrl = $this->isProduction
            ? 'https://api.midtrans.com'
            : 'https://api.sandbox.midtrans.com';
    }

    /**
     * Validate bank account (Account Validation)
     *
     * Request: GET /api/v1/account_validation?bank={bank}&account={account}
     *
     * @param array $payload  ['bank' => 'bca', 'account' => '12345678', 'first_name' => '', 'last_name' => '']
     * @return array
     */
    public function validateBankAccount(array $payload): array
    {
        if (empty($payload['bank']) || empty($payload['account'])) {
            return $this->errorResponse('Parameter "bank" dan "account" wajib.', 400);
        }

        $endpoint = '/api/v1/account_validation';
        $query = [
            'bank' => $payload['bank'],
            'account' => $payload['account'],
        ];

        // optional fields (docs tidak wajib, tapi beberapa integrasi mungkin pake)
        if (!empty($payload['first_name'])) {
            $query['first_name'] = $payload['first_name'];
        }
        if (!empty($payload['last_name'])) {
            $query['last_name'] = $payload['last_name'];
        }

        try {
            $resp = $this->request('GET', $endpoint, $query);

            return [
                'success' => true,
                'data' => $resp['body'] ?? $resp,
                'http_code' => $resp['status'] ?? 200,
            ];
        } catch (Throwable $e) {
            Log::error('[Midtrans] validateBankAccount failed: ' . $e->getMessage(), [
                'bank' => $payload['bank'],
                'account_masked' => $this->mask($payload['account']),
            ]);

            return $this->errorResponse($e->getMessage(), $e->getCode() ?: 500);
        }
    }

    /**
     * Get list of supported beneficiary banks (Payouts)
     *
     * GET /api/v1/beneficiary_banks
     *
     * @return array
     */
    public function getSupportedBanks(): array
    {
        $endpoint = '/api/v1/beneficiary_banks';

        try {
            $resp = $this->request('GET', $endpoint);

            return [
                'success' => true,
                'data' => $resp['body'] ?? $resp,
                'http_code' => $resp['status'] ?? 200,
            ];
        } catch (Throwable $e) {
            Log::error('[Midtrans] getSupportedBanks failed: ' . $e->getMessage());

            return $this->errorResponse($e->getMessage(), $e->getCode() ?: 500);
        }
    }

    /**
     * Generic request using Laravel Http client
     *
     * @param string $method  HTTP method
     * @param string $endpoint  path starting with '/'
     * @param array $params  query for GET, body for POST
     * @return array
     * @throws \Exception
     */
    protected function request(string $method, string $endpoint, array $params = []): array
    {
        $url = rtrim($this->baseUrl, '/') . $endpoint;

        // minimal logging, mask sensitive values
        Log::info('[Midtrans] request', [
            'method' => strtoupper($method),
            'url' => $url,
            'params_preview' => $this->previewParams($params),
            'env' => $this->isProduction ? 'production' : 'sandbox',
        ]);

        $client = Http::withBasicAuth($this->serverKey, '')
            ->acceptJson()
            ->timeout($this->timeoutSeconds);

        $method = strtoupper($method);
        try {
            if ($method === 'GET') {
                $resp = $client->get($url, $params);
            } elseif ($method === 'POST') {
                $resp = $client->post($url, $params);
            } elseif ($method === 'PUT') {
                $resp = $client->put($url, $params);
            } elseif ($method === 'PATCH') {
                $resp = $client->patch($url, $params);
            } elseif ($method === 'DELETE') {
                $resp = $client->delete($url, $params);
            } else {
                // fallback
                $resp = $client->send($method, $url, ['json' => $params]);
            }
        } catch (Throwable $e) {
            throw new \Exception('HTTP request failed: ' . $e->getMessage(), $e->getCode() ?: 500);
        }

        if ($resp->failed()) {
            $status = $resp->status();
            $body = $this->safeJsonDecode($resp->body());
            $msg = $body['error_message'] ?? $body['message'] ?? $body['status_message'] ?? $resp->body() ?? 'Unknown API error';
            throw new \Exception("Midtrans API error: {$msg}", $status);
        }

        return [
            'status' => $resp->status(),
            'body' => $resp->json(),
            'raw' => $resp->body(),
        ];
    }

    /* ---------------- helpers ---------------- */

    protected function mask(string $value): string
    {
        if ($value === '') return '';
        $len = strlen($value);
        if ($len <= 4) return str_repeat('*', $len);
        return substr($value, 0, 2) . str_repeat('*', max(0, $len - 4)) . substr($value, -2);
    }

    protected function previewParams(array $params): array
    {
        if (isset($params['account'])) {
            $params['account'] = $this->mask((string)$params['account']);
        }
        if (isset($params['server_key'])) {
            $params['server_key'] = '***';
        }
        return $params;
    }

    protected function safeJsonDecode(string $raw)
    {
        $decoded = json_decode($raw, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $decoded;
        }
        return ['raw' => $raw];
    }

    protected function errorResponse(string $message, int $code = 500): array
    {
        return [
            'success' => false,
            'message' => $message,
            'http_code' => $code,
        ];
    }
}
