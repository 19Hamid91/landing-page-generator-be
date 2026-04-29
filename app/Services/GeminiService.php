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
                    'temperature'     => 0.7,
                    'topK'            => 40,
                    'topP'            => 0.95,
                    'maxOutputTokens' => 1024,
                ],
            ]);

        if ($response->failed()) {
            Log::error('Gemini API Error', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            throw new Exception('Gemini API request failed: ' . $response->status());
        }

        $rawText = $response->json('candidates.0.content.parts.0.text', '');

        // Strip any accidental markdown code fences
        $rawText = preg_replace('/^```(?:json)?\s*/i', '', trim($rawText));
        $rawText = preg_replace('/```\s*$/i', '', $rawText);

        $decoded = json_decode(trim($rawText), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error('Gemini invalid JSON response', ['raw' => $rawText]);
            throw new Exception('Gemini returned invalid JSON: ' . json_last_error_msg());
        }

        return $decoded;
    }

    /**
     * Build the structured prompt that forces Gemini to return JSON only.
     */
    private function buildPrompt(array $data): string
    {
        $featuresText = is_array($data['features'])
            ? implode(', ', $data['features'])
            : ($data['features'] ?? '-');

        $uspText = is_array($data['usp'])
            ? implode(', ', $data['usp'])
            : ($data['usp'] ?? '-');

        return <<<PROMPT
You are an expert sales copywriter. Your task is to write high-converting sales page copy.

PRODUCT DETAILS:
- Product Name: {$data['product_name']}
- Description: {$data['product_description']}
- Target Audience: {$data['target_audience']}
- Price: {$data['price']}
- Key Features: {$featuresText}
- Unique Selling Proposition (USP): {$uspText}

INSTRUCTIONS:
1. Write compelling, persuasive sales copy for this product.
2. You MUST respond with ONLY a valid JSON object. No explanations, no markdown, no code blocks, no backticks.
3. The JSON must follow this EXACT structure:
{
  "headline": "A short, powerful, attention-grabbing headline",
  "sub_headline": "A supporting subtitle that builds curiosity or urgency",
  "description": "A 2-3 sentence persuasive product description",
  "benefits": ["Benefit 1", "Benefit 2", "Benefit 3", "Benefit 4"],
  "features_breakdown": [
    {"feature": "Feature Name", "explanation": "Why this feature matters to the customer"}
  ],
  "cta_text": "Strong call-to-action button text"
}

Return ONLY the JSON object. Nothing else.
PROMPT;
    }
}
