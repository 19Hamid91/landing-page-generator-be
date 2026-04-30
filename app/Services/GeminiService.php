<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class GeminiService
{
    protected string $apiKey;
    protected string $apiUrl;
    protected string $model;

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key');
        $this->model  = config('services.gemini.model', 'gemini-1.5-flash');
        $this->apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent";
    }

    /**
     * Generate a sales page copy using Gemini AI.
     *
     * @param  array  $data  Product data from the user.
     * @return array         Structured JSON response from Gemini.
     *
     * @throws Exception
     */
    public function generateSalesPage(array $data): array
    {
        $prompt = $this->buildPrompt($data);

        $response = Http::withQueryParameters(['key' => $this->apiKey])
            ->timeout(60)
            ->post($this->apiUrl, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt],
                        ],
                    ],
                ],
                'generationConfig' => [
                    'temperature'       => 0.7,
                    'topK'              => 40,
                    'topP'              => 0.95,
                    'maxOutputTokens'   => 4096,
                    'responseMimeType'  => 'application/json',
                ],
            ]);

        if ($response->failed()) {
            Log::error('Gemini API Error', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            throw new Exception('Gemini API request failed: ' . $response->status());
        }

        $candidate = $response->json('candidates.0');
        $parts = $candidate['content']['parts'] ?? [];
        $rawText = '';
        foreach ($parts as $part) {
            $rawText .= $part['text'] ?? '';
        }

        $decoded = json_decode(trim($rawText), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error('Gemini invalid JSON response', [
                'error'         => json_last_error_msg(),
                'finishReason'  => $candidate['finishReason'] ?? 'unknown',
                'raw'           => $rawText,
                'full_response' => $response->json(),
            ]);
            throw new Exception('Gemini returned invalid JSON: ' . json_last_error_msg());
        }

        return $decoded;
    }

    /**
     * Build the structured prompt that forces Gemini to return JSON only.
     */
    private function buildPrompt(array $data): string
    {
        $featuresText = is_array($data['features'] ?? null) ? implode(', ', $data['features']) : ($data['features'] ?? '-');
        $uspText = is_array($data['usp'] ?? null) ? implode(', ', $data['usp']) : ($data['usp'] ?? '-');
        $lang = ($data['language'] ?? 'en') === 'id' ? 'Indonesian' : 'English';

        return <<<PROMPT
You are a world-class conversion copywriter. Write a highly persuasive sales page in JSON format.
ALL TEXT CONTENT MUST BE IN {$lang}.

PRODUCT:
- Name: {$data['product_name']}
- Pitch: {$data['product_description']}
- Audience: {$data['target_audience']}
- Price: {$data['price']}
- Features: {$featuresText}
- USP: {$uspText}

JSON STRUCTURE (Return only valid JSON):
{
  "headline": "Powerful hook",
  "sub_headline": "Supporting details",
  "description": "Engaging 2-3 sentence overview",
  "benefits": ["Benefit 1", "Benefit 2", "Benefit 3", "Benefit 4"],
  "features_breakdown": [
    {"feature": "Feature Title", "explanation": "Value-driven explanation"}
  ],
  "cta_text": "Action-oriented button text"
}
PROMPT;
    }
}
