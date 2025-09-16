<?php

namespace App\Services;

use Exception;
use Midtrans\Config;
use Midtrans\CoreApi;
use Midtrans\Snap;

class MidtransService
{
    protected $serverKey;
    protected $clientKey;
    protected $isProduction;
    protected $baseUrl;

    public function __construct()
    {
        $this->serverKey = config('midtrans.server_key');
        $this->clientKey = config('midtrans.client_key');
        $this->isProduction = config('midtrans.is_production');
        
        // Set Midtrans configuration
        Config::$serverKey = $this->serverKey;
        Config::$clientKey = $this->clientKey;
        Config::$isProduction = $this->isProduction;
        Config::$isSanitized = true;
        Config::$is3ds = true;
        
        // Set base URL based on environment
        $this->baseUrl = $this->isProduction ? 
            'https://api.midtrans.com' : 
            'https://api.sandbox.midtrans.com';
    }

    /**
     * Check bank account information using Midtrans API
     * 
     * @param array $accountDetails
     * @return array
     */
    public function checkBankAccount($accountDetails)
    {
        try {
            // Validate required parameters
            if (!isset($accountDetails['bank']) || !isset($accountDetails['account_number'])) {
                throw new Exception('Bank and account number are required');
            }

            // Prepare the request data
            $requestData = [
                'bank' => $accountDetails['bank'],
                'account_number' => $accountDetails['account_number']
            ];

            // Add optional parameters if provided
            if (isset($accountDetails['first_name'])) {
                $requestData['first_name'] = $accountDetails['first_name'];
            }
            
            if (isset($accountDetails['last_name'])) {
                $requestData['last_name'] = $accountDetails['last_name'];
            }

            // Make the API request
            $response = $this->makeApiRequest('/v1/account_verifications', 'POST', $requestData);
            
            return [
                'success' => true,
                'data' => $response
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Make API request to Midtrans
     * 
     * @param string $endpoint
     * @param string $method
     * @param array $data
     * @return array
     */
    protected function makeApiRequest($endpoint, $method = 'GET', $data = [])
    {
        $url = $this->baseUrl . $endpoint;
        
        $headers = [
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Basic ' . base64_encode($this->serverKey . ':')
        ];

        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        
        curl_close($ch);

        if ($error) {
            throw new Exception('cURL Error: ' . $error);
        }

        $responseData = json_decode($response, true);
        
        if ($httpCode >= 400) {
            throw new Exception('API Error: ' . ($responseData['message'] ?? 'Unknown error'), $httpCode);
        }

        return $responseData;
    }

    /**
     * Get supported banks for account verification
     * 
     * @return array
     */
    public function getSupportedBanks()
    {
        try {
            $response = $this->makeApiRequest('/v1/account_verifications/banks', 'GET');
            
            return [
                'success' => true,
                'data' => $response
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}