<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class GeocodeService
{
    private const CACHE_TTL_SECONDS = 86400;

    /**
     * 郵便番号または住所から座標を検索する。
     *
     * @return array{
     *     ok: bool,
     *     source?: string,
     *     latitude?: float,
     *     longitude?: float,
     *     postal_code?: string|null,
     *     prefecture?: string|null,
     *     address?: string|null,
     *     label?: string|null,
     *     message?: string
     * }
     */
    public function search(string $query): array
    {
        $query = trim($query);
        if ($query === '') {
            return ['ok' => false, 'message' => '検索語を入力してください'];
        }

        $digits = preg_replace('/\D/', '', $query) ?? '';
        if (strlen($digits) === 7) {
            return $this->searchByPostal($digits);
        }

        return $this->searchByAddress($query);
    }

    /**
     * @return array<string, mixed>
     */
    public function searchByPostal(string $digits): array
    {
        $cacheKey = 'geocode:postal:'.$digits;

        return Cache::remember($cacheKey, self::CACHE_TTL_SECONDS, function () use ($digits) {
            $postalResponse = Http::timeout(4)
                ->acceptJson()
                ->get('https://zipcloud.ibsnet.co.jp/api/search', [
                    'zipcode' => $digits,
                ]);

            if (! $postalResponse->successful()) {
                return ['ok' => false, 'message' => '郵便番号検索に失敗しました'];
            }

            $payload = $postalResponse->json();
            $result = $payload['results'][0] ?? null;
            if (! is_array($result)) {
                return ['ok' => false, 'message' => '該当する郵便番号が見つかりませんでした'];
            }

            $prefecture = (string) ($result['address1'] ?? '');
            $address = trim(($result['address2'] ?? '').($result['address3'] ?? ''));
            $label = $prefecture.$address;
            $postalCode = substr($digits, 0, 3).'-'.substr($digits, 3);

            $point = $this->searchGsiPoint($label);
            if ($point === null && $address !== '') {
                $point = $this->searchGsiPoint($prefecture.$address);
            }

            if ($point === null) {
                return [
                    'ok' => true,
                    'source' => 'zipcloud',
                    'latitude' => null,
                    'longitude' => null,
                    'postal_code' => $postalCode,
                    'prefecture' => $prefecture !== '' ? $prefecture : null,
                    'address' => $address !== '' ? $address : null,
                    'label' => $label !== '' ? $label : null,
                    'message' => '住所は見つかりましたが座標を取得できませんでした',
                ];
            }

            return [
                'ok' => true,
                'source' => 'zipcloud+gsi',
                'latitude' => $point['latitude'],
                'longitude' => $point['longitude'],
                'postal_code' => $postalCode,
                'prefecture' => $prefecture !== '' ? $prefecture : null,
                'address' => $address !== '' ? $address : null,
                'label' => $point['label'] ?: $label,
            ];
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function searchByAddress(string $query): array
    {
        $cacheKey = 'geocode:address:'.sha1($query);

        return Cache::remember($cacheKey, self::CACHE_TTL_SECONDS, function () use ($query) {
            $point = $this->searchGsiPoint($query);
            if ($point === null) {
                return ['ok' => false, 'message' => '該当する住所が見つかりませんでした'];
            }

            $parsed = $this->splitJapaneseAddress($point['label'] ?: $query);

            return [
                'ok' => true,
                'source' => 'gsi',
                'latitude' => $point['latitude'],
                'longitude' => $point['longitude'],
                'postal_code' => null,
                'prefecture' => $parsed['prefecture'],
                'address' => $parsed['address'],
                'label' => $point['label'],
            ];
        });
    }

    /**
     * 座標から最寄り住所・郵便番号を取得（HeartRails）。
     *
     * @return array<string, mixed>
     */
    public function reverse(float $latitude, float $longitude): array
    {
        $cacheKey = sprintf('geocode:reverse:%.4f:%.4f', $latitude, $longitude);

        return Cache::remember($cacheKey, self::CACHE_TTL_SECONDS, function () use ($latitude, $longitude) {
            $response = Http::timeout(4)
                ->acceptJson()
                ->get('https://geoapi.heartrails.com/api/json', [
                    'method' => 'searchByGeoLocation',
                    'x' => $longitude,
                    'y' => $latitude,
                ]);

            if (! $response->successful()) {
                return ['ok' => false, 'message' => '座標からの住所検索に失敗しました'];
            }

            $location = $response->json('response.location.0');
            if (! is_array($location)) {
                return ['ok' => false, 'message' => '近くの住所が見つかりませんでした'];
            }

            $postalDigits = preg_replace('/\D/', '', (string) ($location['postal'] ?? '')) ?? '';
            $postalCode = strlen($postalDigits) === 7
                ? substr($postalDigits, 0, 3).'-'.substr($postalDigits, 3)
                : null;

            return [
                'ok' => true,
                'source' => 'heartrails',
                'postal_code' => $postalCode,
                'prefecture' => $location['prefecture'] ?? null,
                'address' => trim(($location['city'] ?? '').($location['town'] ?? '')),
                'latitude' => isset($location['y']) ? (float) $location['y'] : $latitude,
                'longitude' => isset($location['x']) ? (float) $location['x'] : $longitude,
            ];
        });
    }

    /**
     * @return array{latitude: float, longitude: float, label: string}|null
     */
    private function searchGsiPoint(string $query): ?array
    {
        $response = Http::timeout(4)
            ->acceptJson()
            ->withHeaders([
                'User-Agent' => "LOAN'S/1.0 (property map search)",
            ])
            ->get('https://msearch.gsi.go.jp/address-search/AddressSearch', [
                'q' => $query,
            ]);

        if (! $response->successful()) {
            return null;
        }

        $features = $response->json();
        if (! is_array($features) || $features === []) {
            return null;
        }

        $feature = $features[0];
        $coordinates = $feature['geometry']['coordinates'] ?? null;
        if (! is_array($coordinates) || count($coordinates) < 2) {
            return null;
        }

        return [
            'longitude' => (float) $coordinates[0],
            'latitude' => (float) $coordinates[1],
            'label' => (string) ($feature['properties']['title'] ?? $query),
        ];
    }

    /**
     * @return array{prefecture: string|null, address: string|null}
     */
    private function splitJapaneseAddress(string $full): array
    {
        $prefectures = \App\Support\Prefectures::ALL;
        foreach ($prefectures as $name) {
            if (str_starts_with($full, $name)) {
                $rest = trim(substr($full, strlen($name)));

                return [
                    'prefecture' => $name,
                    'address' => $rest !== '' ? $rest : null,
                ];
            }
        }

        return [
            'prefecture' => null,
            'address' => $full !== '' ? $full : null,
        ];
    }
}
