<?php

namespace App\Http\Controllers;

use App\Services\GeocodeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GeocodeController extends Controller
{
    public function __construct(
        private readonly GeocodeService $geocode
    ) {}

    public function search(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q' => ['required', 'string', 'max:255'],
        ]);

        $result = $this->geocode->search($validated['q']);

        return response()->json($result, ($result['ok'] ?? false) ? 200 : 404);
    }

    public function reverse(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ]);

        $result = $this->geocode->reverse(
            (float) $validated['latitude'],
            (float) $validated['longitude'],
        );

        return response()->json($result, ($result['ok'] ?? false) ? 200 : 404);
    }
}
