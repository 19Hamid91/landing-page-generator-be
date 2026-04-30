<?php

namespace App\Services;

use App\Models\SalesPage;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class SalesPageService
{
    public function __construct(protected GeminiService $gemini) {}

    /**
     * Create a sales page with AI generated content.
     */
    public function createForUser(User $user, array $data): SalesPage
    {
        // Handle image uploads
        if (isset($data['images'])) {
            $data['images'] = $this->uploadImages($data['images']);
        }

        $aiOutput = $this->gemini->generateSalesPage($data);

        return $user->salesPages()->create([
            'product_name'        => $data['product_name'],
            'product_description' => $data['product_description'],
            'target_audience'     => $data['target_audience'],
            'price'               => $data['price'] ?? null,
            'features'            => $data['features'] ?? [],
            'usp'                 => $data['usp'] ?? [],
            'ai_output'           => $aiOutput,
            'template_name'       => $data['template_name'] ?? 'modern',
            'images'              => $data['images'] ?? [],
            'language'            => $data['language'] ?? 'en',
            'currency'            => $data['currency'] ?? 'USD',
        ]);
    }

    /**
     * Update a sales page and optionally regenerate content.
     */
    public function updatePage(SalesPage $salesPage, array $data): SalesPage
    {
        if (isset($data['images'])) {
            $data['images'] = $this->uploadImages($data['images'], $salesPage->images ?? []);
        }

        // Handle JSON ai_output if it's a string (from FormData)
        if (isset($data['ai_output']) && is_string($data['ai_output'])) {
            $data['ai_output'] = json_decode($data['ai_output'], true);
        }

        $salesPage->fill($data);

        if (isset($data['regenerate']) && $data['regenerate']) {
            $salesPage->ai_output = $this->gemini->generateSalesPage($salesPage->toArray());
        }

        $salesPage->save();

        return $salesPage;
    }

    /**
     * Generate a preview of AI content without saving.
     */
    public function generatePreview(SalesPage $salesPage, array $updatedData = []): array
    {
        $generationData = array_merge($salesPage->toArray(), $updatedData);
        
        return $this->gemini->generateSalesPage($generationData);
    }

    /**
     * Upload images and return their stored paths.
     */
    protected function uploadImages(array $images, array $existingImages = []): array
    {
        $paths = [];

        foreach ($images as $image) {
            if ($image instanceof UploadedFile) {
                $paths[] = $image->store('sales-pages', 'public');
            } elseif (is_string($image)) {
                $paths[] = $image;
            }
        }

        return $paths;
    }
}
