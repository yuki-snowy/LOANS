<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ReverseGeocodeController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ]);

        $response = Http::timeout(8)
            ->withHeaders([
                'User-Agent' => "LOAN'S property app (local reverse geocode)",
                'Accept-Language' => 'ja',
            ])
            ->get('https://nominatim.openstreetmap.org/reverse', [
                'format' => 'jsonv2',
                'lat' => $validated['latitude'],
                'lon' => $validated['longitude'],
                'zoom' => 18,
                'addressdetails' => 1,
            ]);

        if (! $response->successful()) {
            return response()->json([
                'ok' => false,
                'message' => '座標からの住所検索に失敗しました',
            ], 502);
        }

        $payload = $response->json();
        $address = is_array($payload['address'] ?? null) ? $payload['address'] : [];
        $postcode = isset($address['postcode']) ? preg_replace('/\D/', '', (string) $address['postcode']) : null;

        if ($postcode && strlen($postcode) === 7) {
            $postcode = substr($postcode, 0, 3).'-'.substr($postcode, 3);
        } else {
            $postcode = null;
        }

        $prefecture = $address['province']
            ?? $address['state']
            ?? $address['region']
            ?? null;

        $cityParts = array_filter([
            $address['city'] ?? null,
            $address['town'] ?? null,
            $address['village'] ?? null,
            $address['municipality'] ?? null,
            $address['city_district'] ?? null,
            $address['suburb'] ?? null,
            $address['neighbourhood'] ?? null,
            $address['quarter'] ?? null,
            $address['hamlet'] ?? null,
        ]);

        // 都道府県と重複しない市区町村以降を連結
        $rest = [];
        foreach ($cityParts as $part) {
            $part = (string) $part;
            if ($prefecture && str_contains($part, (string) $prefecture)) {
                continue;
            }
            if (! in_array($part, $rest, true)) {
                $rest[] = $part;
            }
        }

        if (! empty($address['road'])) {
            $rest[] = (string) $address['road'];
        }
        if (! empty($address['house_number'])) {
            $rest[] = (string) $address['house_number'];
        }

        return response()->json([
            'ok' => true,
            'postal_code' => $postcode,
            'prefecture' => $prefecture,
            'address' => $rest !== [] ? implode('', $rest) : null,
        ]);
    }
}
