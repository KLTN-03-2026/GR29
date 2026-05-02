<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

class GoogleMapsConfigController extends Controller
{
    public function show(): JsonResponse
    {
        return response()->json([
            'api_key' => config('services.google_maps.public_key'),
        ]);
    }
}
