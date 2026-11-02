<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\WasteAiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AiDetectionApiController extends Controller
{
    /**
     * Standalone AI detection proxy endpoint for mobile real-time preview or pre-classification.
     */
    public function detect(Request $request, WasteAiService $aiService): JsonResponse
    {
        $request->validate([
            'image' => ['required', 'image', 'mimes:jpeg,jpg,png,webp', 'max:10240'],
        ]);

        $file = $request->file('image');
        $tempPath = $file->getRealPath();

        $result = $aiService->detectWaste($tempPath, $file->getClientOriginalName());

        return response()->json([
            'status' => 'success',
            'data' => [
                'label' => $result['label'],
                'category' => $result['category'],
                'confidence' => $result['confidence'],
                'probabilities' => $result['probabilities'] ?? [],
            ],
        ], 200);
    }
}
