<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WasteAiService
{
    protected string $mlUrl;
    protected ?string $mlKey;

    public function __construct()
    {
        $this->mlUrl = rtrim(config('services.ml.url', env('ML_SERVICE_URL', 'http://127.0.0.1:5000')), '/');
        $this->mlKey = config('services.ml.key', env('ML_SERVICE_KEY', ''));
    }

    /**
     * Send an image to the FastAPI ML service for classification.
     *
     * @param string $filePath Full filesystem path or binary contents
     * @param string $filename Original file name
     * @return array ['label' => ..., 'category' => ..., 'confidence' => ...]
     */
    public function detectWaste(string $filePath, string $filename = 'image.jpg'): array
    {
        try {
            $httpClient = Http::timeout(5);
            if (!empty($this->mlKey)) {
                $httpClient = $httpClient->withHeaders([
                    'X-Internal-Secret' => $this->mlKey,
                ]);
            }

            $response = $httpClient
                ->attach('file', file_get_contents($filePath), $filename)
                ->post("{$this->mlUrl}/predict");

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['status']) && $data['status'] === 'success' && isset($data['data'])) {
                    return [
                        'success' => true,
                        'label' => $data['data']['label'] ?? 'Unclassified',
                        'category' => $data['data']['category'] ?? 'Tidak Terklasifikasi',
                        'confidence' => (float) ($data['data']['confidence'] ?? 0.0),
                        'probabilities' => $data['data']['probabilities'] ?? [],
                    ];
                }
            }

            Log::warning("[WasteAiService] ML Service returned unexpected status: " . $response->status());
        } catch (\Throwable $e) {
            Log::warning("[WasteAiService] Failed to reach ML Service: " . $e->getMessage());
        }

        return [
            'success' => false,
            'label' => 'Unclassified',
            'category' => 'Tidak Terklasifikasi',
            'confidence' => 0.0,
            'probabilities' => [],
        ];
    }
}
