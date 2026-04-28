<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DistanceService
{
    private const ROUTES_MAX_DESTINATIONS_PER_REQUEST = 625;
    private const ROUTES_API_URL = 'https://routes.googleapis.com/distanceMatrix/v2:computeRouteMatrix';
    private const ROUTES_FIELD_MASK = 'originIndex,destinationIndex,distanceMeters,duration,status,condition';

    /**
     * Tính khoảng cách đường bộ giữa 2 điểm bằng Google Maps Distance Matrix API.
     *
     * @param float|null $fromLat Vĩ độ điểm xuất phát
     * @param float|null $fromLng Kinh độ điểm xuất phát
     * @param float|null $toLat Vĩ độ điểm đích
     * @param float|null $toLng Kinh độ điểm đích
     * @return float|null Khoảng cách tính bằng km (làm tròn 2 chữ số), hoặc null nếu thiếu tọa độ hoặc lỗi API
     */
    public function calculateDistance(?float $fromLat, ?float $fromLng, ?float $toLat, ?float $toLng): ?float
    {
        $results = $this->calculateDistances($fromLat, $fromLng, [[
            'key' => 'single_destination',
            'lat' => $toLat,
            'lng' => $toLng,
        ]]);

        return $results['single_destination'] ?? null;
    }

    /**
     * Tính khoảng cách cho nhiều điểm đích từ một điểm xuất phát bằng Google Distance Matrix API.
     *
     * @param float|null $originLat Vĩ độ điểm xuất phát
     * @param float|null $originLng Kinh độ điểm xuất phát
     * @param array $destinations Mảng các điểm đích: [['lat' => float, 'lng' => float, 'key' => string], ...]
     * @return array Mảng kết quả: ['key' => distance_km, ...] (chỉ bao gồm các điểm thành công)
     */
    public function calculateDistances(?float $originLat, ?float $originLng, array $destinations): array
    {
        if ($originLat === null || $originLng === null || empty($destinations)) {
            return [];
        }

        $validDestinations = array_values(array_filter($destinations, function ($dest) {
            return isset($dest['lat'], $dest['lng'], $dest['key']) &&
                   $dest['lat'] !== null && $dest['lng'] !== null;
        }));

        if (empty($validDestinations)) {
            return [];
        }

        // Keep an approximate value for every destination so the UI can still sort if Google fails.
        $results = $this->calculateFallbackDistances($originLat, $originLng, $validDestinations);

        $apiKey = config('services.google_maps.key');
        if (empty($apiKey)) {
            Log::error('Google Maps API key not configured');
            return $results;
        }

        foreach (array_chunk($validDestinations, self::ROUTES_MAX_DESTINATIONS_PER_REQUEST) as $chunkIndex => $destinationChunk) {
            try {
                $response = Http::asJson()
                    ->withHeaders([
                        'X-Goog-Api-Key' => $apiKey,
                        'X-Goog-FieldMask' => self::ROUTES_FIELD_MASK,
                    ])
                    ->timeout(10)
                    ->post(self::ROUTES_API_URL, $this->buildRouteMatrixPayload(
                        $originLat,
                        $originLng,
                        $destinationChunk
                    ));

                if (!$response->successful()) {
                    Log::warning('Google Routes API request failed for multiple destinations', [
                        'status' => $response->status(),
                        'origin' => [$originLat, $originLng],
                        'destinations_count' => count($destinationChunk),
                        'chunk_index' => $chunkIndex,
                        'response_body' => mb_substr($response->body(), 0, 500),
                    ]);
                    continue;
                }

                $elements = $response->json();
                if (!is_array($elements) || empty($elements)) {
                    Log::warning('Google Routes API returned empty elements for multiple destinations', [
                        'origin' => [$originLat, $originLng],
                        'destinations_count' => count($destinationChunk),
                        'chunk_index' => $chunkIndex,
                        'response_body' => mb_substr($response->body(), 0, 500),
                    ]);
                    continue;
                }

                $this->mergeRouteMatrixResults($results, $destinationChunk, $elements, $chunkIndex);
            } catch (\Throwable $e) {
                Log::error('Error calling Google Routes API for multiple destinations', [
                    'error' => $e->getMessage(),
                    'origin' => [$originLat, $originLng],
                    'destinations_count' => count($destinationChunk),
                    'chunk_index' => $chunkIndex,
                ]);
            }
        }

        return $results;
    }

    /**
     * Tính khoảng cách từ vị trí rescuer tới vị trí yêu cầu cứu hộ.
     * Dùng cho rescuer xem khoảng cách từ vị trí hiện tại tới sự cố.
     *
     * @param float|null $rescuerLat Vĩ độ rescuer
     * @param float|null $rescuerLng Kinh độ rescuer
     * @param float|null $requestLat Vĩ độ yêu cầu
     * @param float|null $requestLng Kinh độ yêu cầu
     * @return float|null Khoảng cách km hoặc null nếu thiếu tọa độ hoặc lỗi API
     */
    public function getDistanceFromRescuerToRequest(?float $rescuerLat, ?float $rescuerLng, ?float $requestLat, ?float $requestLng): ?float
    {
        return $this->calculateDistance($rescuerLat, $rescuerLng, $requestLat, $requestLng);
    }

    /**
     * Tính khoảng cách từ vị trí yêu cầu tới các đội cứu hộ.
     * Dùng cho admin điều phối để tìm đội gần nhất.
     *
     * @param float|null $requestLat Vĩ độ yêu cầu
     * @param float|null $requestLng Kinh độ yêu cầu
     * @param array $teams Mảng các đội: [['id' => int, 'lat' => float, 'lng' => float], ...]
     * @return array Mảng ['team_id' => distance_km, ...]
     */
    public function getDistancesFromRequestToTeams(?float $requestLat, ?float $requestLng, array $teams): array
    {
        $destinations = [];
        foreach ($teams as $team) {
            if (isset($team['id'], $team['lat'], $team['lng'])) {
                $destinations[] = [
                    'key' => $team['id'],
                    'lat' => $team['lat'],
                    'lng' => $team['lng'],
                ];
            }
        }
        return $this->calculateDistances($requestLat, $requestLng, $destinations);
    }

    private function calculateFallbackDistances(?float $originLat, ?float $originLng, array $destinations): array
    {
        $results = [];
        foreach ($destinations as $dest) {
            $distance = $this->calculateHaversineDistance($originLat, $originLng, $dest['lat'], $dest['lng']);
            if ($distance !== null) {
                $results[$dest['key']] = $distance;
            }
        }

        return $results;
    }

    private function buildRouteMatrixPayload(float $originLat, float $originLng, array $destinations): array
    {
        return [
            'origins' => [[
                'waypoint' => [
                    'location' => [
                        'latLng' => [
                            'latitude' => $originLat,
                            'longitude' => $originLng,
                        ],
                    ],
                ],
            ]],
            'destinations' => array_map(function ($dest) {
                return [
                    'waypoint' => [
                        'location' => [
                            'latLng' => [
                                'latitude' => (float) $dest['lat'],
                                'longitude' => (float) $dest['lng'],
                            ],
                        ],
                    ],
                ];
            }, $destinations),
            'travelMode' => 'DRIVE',
            'routingPreference' => 'TRAFFIC_AWARE',
            'languageCode' => 'vi',
            'units' => 'METRIC',
        ];
    }

    private function mergeRouteMatrixResults(array &$results, array $destinationChunk, array $elements, int $chunkIndex): void
    {
        foreach ($elements as $element) {
            $destinationIndex = $element['destinationIndex'] ?? null;
            if (!is_int($destinationIndex) && !ctype_digit((string) $destinationIndex)) {
                Log::warning('Google Routes API returned element without valid destinationIndex', [
                    'chunk_index' => $chunkIndex,
                    'element' => $element,
                ]);
                continue;
            }

            $destinationIndex = (int) $destinationIndex;
            $destination = $destinationChunk[$destinationIndex] ?? null;
            if ($destination === null) {
                Log::warning('Google Routes API returned element with out-of-range destinationIndex', [
                    'chunk_index' => $chunkIndex,
                    'destination_index' => $destinationIndex,
                    'destinations_count' => count($destinationChunk),
                ]);
                continue;
            }

            if (isset($element['distanceMeters'])) {
                $results[$destination['key']] = round(((float) $element['distanceMeters']) / 1000, 2);
                continue;
            }

            Log::warning('Google Routes API returned element without distanceMeters', [
                'chunk_index' => $chunkIndex,
                'destination_index' => $destinationIndex,
                'condition' => $element['condition'] ?? null,
                'status' => $element['status'] ?? null,
                'destination' => $destination,
            ]);
        }
    }

    private function calculateHaversineDistance(?float $fromLat, ?float $fromLng, ?float $toLat, ?float $toLng): ?float
    {
        if ($fromLat === null || $fromLng === null || $toLat === null || $toLng === null) {
            return null;
        }

        $earthRadiusKm = 6371;

        $latDelta = deg2rad($toLat - $fromLat);
        $lngDelta = deg2rad($toLng - $fromLng);

        $a = sin($latDelta / 2) ** 2
            + cos(deg2rad($fromLat)) * cos(deg2rad($toLat)) * sin($lngDelta / 2) ** 2;

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return round($earthRadiusKm * $c, 2);
    }
}
