<?php

use SilverStripe\Core\Environment;

class DuitkuService
{
    private $merchantCode;
    private $apiKey;
    private $baseUrl;
    private $callbackUrl;
    private $returnUrl;

    public function __construct()
    {
        $this->merchantCode = Environment::getEnv('DUITKU_MERCHANT_CODE');
        $this->apiKey = Environment::getEnv('DUITKU_API_KEY');
        $this->baseUrl = Environment::getEnv('DUITKU_BASE_URL');

        // Use ngrok URL for callback and return
        $ngrokUrl = Environment::getEnv('NGROK_URL') ?: 'https://1683d345346d.ngrok-free.app';
        $this->callbackUrl = $ngrokUrl . '/payment/callback';
        $this->returnUrl = $ngrokUrl . '/payment/return';

        error_log('DuitkuService - Callback URL: ' . $this->callbackUrl);
        error_log('DuitkuService - Return URL: ' . $this->returnUrl);
    }

    /**
     * Get available payment methods
     */
    public function getPaymentMethods($amount)
    {
        $datetime = date('Y-m-d H:i:s');
        $signature = hash('sha256', $this->merchantCode . $amount . $datetime . $this->apiKey);

        $params = [
            'merchantcode' => $this->merchantCode,
            'amount' => $amount,
            'datetime' => $datetime,
            'signature' => $signature
        ];

        $url = Environment::getEnv('DUITKU_GETPAYMENTMETHOD_URL');
        $response = $this->makeRequest($url, $params);

        if (isset($response['paymentFee'])) {
            return $response['paymentFee'];
        }

        return [];
    }

    /**
     * Create payment transaction
     */
    public function createTransaction($order)
    {
        $merchantOrderId = 'ORDER-' . $order->ID . '-' . time();
        $paymentAmount = $order->TotalPrice + $order->ShippingCost;
        $signature = md5($this->merchantCode . $merchantOrderId . $paymentAmount . $this->apiKey);

        $customerName = trim($order->Member()->FirstName . ' ' . $order->Member()->Surname);
        $email = $order->Member()->Email;
        $phoneNumber = $order->ShippingAddress()->PhoneNumber ?? '';

        $params = [
            'merchantCode' => $this->merchantCode,
            'paymentAmount' => $paymentAmount,
            'paymentMethod' => $order->PaymentMethod,
            'merchantOrderId' => $merchantOrderId,
            'productDetails' => 'Order ' . $order->OrderCode,
            'customerVaName' => $customerName,
            'email' => $email,
            'phoneNumber' => $phoneNumber,
            'callbackUrl' => $this->callbackUrl,
            'returnUrl' => $this->returnUrl,
            'signature' => $signature,
            'expiryPeriod' => 1440,
        ];

        error_log('DuitkuService::createTransaction - Request params: ' . json_encode($params));

        $response = $this->makeRequest($this->baseUrl, $params, 'POST');
        error_log('DuitkuService::createTransaction - Response: ' . json_encode($response));

        if ($response && isset($response['statusCode']) && $response['statusCode'] == '00') {
            return [
                'success' => true,
                'merchantOrderId' => $merchantOrderId,
                'paymentUrl' => $response['paymentUrl'] ?? null,
                'vaNumber' => $response['vaNumber'] ?? null,
                'qrString' => $response['qrString'] ?? null,
                'statusCode' => $response['statusCode'],
                'statusMessage' => $response['statusMessage'] ?? 'Transaction created successfully'
            ];
        }

        if ($response) {
            error_log('DuitkuService::createTransaction - Error response: ' . json_encode($response));
        }

        return [
            'success' => false,
            'error' => $response['statusMessage'] ?? 'Failed to create transaction'
        ];
    }

    /**
     * Verify callback signature
     */
    public function verifyCallback($data)
    {
        $merchantOrderId = $data['merchantOrderId'] ?? '';
        $resultCode = $data['resultCode'] ?? '';
        $amount = $data['amount'] ?? 0;
        $receivedSignature = $data['signature'] ?? '';

        if (empty($merchantOrderId) || empty($receivedSignature)) {
            error_log('DuitkuService::verifyCallback - Missing required fields');
            return false;
        }

        $calculatedSignature = md5($this->merchantCode . $amount . $merchantOrderId . $this->apiKey);

        error_log('DuitkuService::verifyCallback - Calculated signature: ' . $calculatedSignature);
        error_log('DuitkuService::verifyCallback - Received signature: ' . $receivedSignature);

        $isValid = hash_equals($calculatedSignature, $receivedSignature);
        error_log('DuitkuService::verifyCallback - Signature valid: ' . ($isValid ? 'true' : 'false'));

        return $isValid;
    }

    /**
     * Make HTTP request to Duitku API
     */
    private function makeRequest($url, $params, $method = 'POST')
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json'
        ]);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_error($ch)) {
            error_log('DuitkuService - CURL Error: ' . curl_error($ch));
        }

        curl_close($ch);

        if ($httpCode == 200 && $response) {
            return json_decode($response, true);
        }

        error_log("DuitkuService - HTTP Error Code: $httpCode, Response: $response");
        return false;
    }
}