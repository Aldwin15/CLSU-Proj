<?php

namespace App\Services\Weather;

interface WeatherServiceInterface
{
    /**
     * Get 7-day weather forecast and construction-specific metrics for coordinate location
     */
    public function getForecast(float $latitude, float $longitude): array;

    /**
     * Get current weather telemetry
     */
    public function getCurrentWeather(float $latitude, float $longitude): array;
}
