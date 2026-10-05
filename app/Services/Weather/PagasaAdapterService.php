<?php

namespace App\Services\Weather;

class PagasaAdapterService implements WeatherServiceInterface
{
    protected ?string $apiKey;

    public function __construct(?string $apiKey = null)
    {
        $this->apiKey = $apiKey ?? config('services.pagasa.key', env('PAGASA_API_KEY'));
    }

    public function getForecast(float $latitude, float $longitude): array
    {
        if (empty($this->apiKey)) {
            return [
                'status' => 'error',
                'provider' => 'DOST-PAGASA API (Disconnected)',
                'message' => 'Failed to fetch weather data: Missing PAGASA API Key or station credentials.',
                'daily' => []
            ];
        }

        return [
            'status' => 'error',
            'provider' => 'DOST-PAGASA API',
            'message' => 'PAGASA API endpoint unreachable or authentication invalid.',
            'daily' => []
        ];
    }

    public function getCurrentWeather(float $latitude, float $longitude): array
    {
        return [];
    }
}
