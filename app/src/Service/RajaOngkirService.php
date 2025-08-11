<?php

use SilverStripe\Core\Environment;

class RajaOngkirService
{
    private static $api_base_url = 'https://rajaongkir.komerce.id/api/v1';

    private function getApiKey()
    {
        return Environment::getEnv('RAJAONGKIR_API_KEY');
    }

    private function makeRequest($endpoint, $data = null)
    {
        $headers = [
            'Accept: application/json',
            'key: ' . $this->getApiKey()
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, self::$api_base_url . $endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        if ($data) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200) {
            $result = json_decode($response, true);
            return $result['data'] ?? [];
        }

        return [];
    }

    public function getProvinces()
    {
        return $this->makeRequest('/destination/province');
    }

    public function getCities($provinceId)
    {
        return $this->makeRequest('/destination/city/' . $provinceId);
    }

    public function getDistricts($cityId)
    {
        return $this->makeRequest('/destination/district/' . $cityId);
    }

    public function checkOngkir($origin, $destination, $weight, $courier)
    {
        $data = [
            'origin' => $origin,
            'destination' => $destination,
            'weight' => $weight,
            'courier' => $courier
        ];

        return $this->makeRequest('/calculate/domestic-cost', $data);
    }
}