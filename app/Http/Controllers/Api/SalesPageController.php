<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SalesPage;
use App\Services\GeminiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;

class SalesPageController extends Controller
{
    public function __construct(protected GeminiService $gemini) {}

    /**
     * List all sales pages belonging to the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $salesPages = $request->user()
            ->salesPages()
            ->latest()
            ->get();

        return response()->json([
            'data' => $salesPages,
        ]);
    }

    /**
     * Create a new sales page and generate AI copy via Gemini.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_name'        => 'required|string|max:255',
            'product_description' => 'required|string',
            'target_audience'     => 'required|string|max:255',
            'price'               => 'nullable|numeric|min:0',
            'features'            => 'nullable|array',
            'features.*'          => 'string',
            'usp'                 => 'nullable|array',
            'usp.*'               => 'string',
            'template_name'       => 'nullable|string|max:100',
            'images'              => 'nullable|array',
            'images.*'            => 'url',
            'language'            => 'nullable|string|in:id,en',
            'currency'            => 'nullable|string|in:IDR,USD',
        ]);

        try {
            $aiOutput = $this->gemini->generateSalesPage($validated);
        } catch (Exception $e) {
            \Log::error('Gemini AI Generation Failed', [
                'user_id' => auth()->id(),
                'input_data' => $validated,
                'error_message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(), // Optional: kalau mau lihat kodingan mana yang error
            ]);

            return response()->json([
                'message' => 'AI generation failed.',
                'error'   => $e->getMessage(),
            ], 502);
        }

        $salesPage = $request->user()->salesPages()->create([
            'product_name'        => $validated['product_name'],
            'product_description' => $validated['product_description'],
            'target_audience'     => $validated['target_audience'],
            'price'               => $validated['price'] ?? null,
            'features'            => $validated['features'] ?? [],
            'usp'                 => $validated['usp'] ?? [],
            'ai_output'           => $aiOutput,
            'template_name'       => $validated['template_name'] ?? 'modern',
            'images'              => $validated['images'] ?? [],
            'language'            => $validated['language'] ?? 'en',
            'currency'            => $validated['currency'] ?? 'USD',
        ]);

        return response()->json([
            'message' => 'Sales page created successfully.',
            'data'    => $salesPage,
        ], 201);
    }

    /**
     * Show a single sales page.
     */
    public function show(Request $request, SalesPage $salesPage): JsonResponse
    {
        $this->authorizeOwner($request, $salesPage);

        return response()->json([
            'data' => $salesPage,
        ]);
    }

    /**
     * Update product details and optionally re-generate AI copy.
     */
    public function update(Request $request, SalesPage $salesPage): JsonResponse
    {
        $this->authorizeOwner($request, $salesPage);

        $validated = $request->validate([
            'product_name'        => 'sometimes|required|string|max:255',
            'product_description' => 'sometimes|required|string',
            'target_audience'     => 'sometimes|required|string|max:255',
            'price'               => 'nullable|numeric|min:0',
            'features'            => 'nullable|array',
            'features.*'          => 'string',
            'usp'                 => 'nullable|array',
            'usp.*'               => 'string',
            'template_name'       => 'nullable|string|max:100',
            'images'              => 'nullable|array',
            'images.*'            => 'url',
            'language'            => 'nullable|string|in:id,en',
            'currency'            => 'nullable|string|in:IDR,USD',
            'regenerate'          => 'boolean', // pass true to re-generate AI copy
        ]);

        $salesPage->fill($validated);

        // Re-generate AI output if requested
        if ($request->boolean('regenerate', false)) {
            try {
                $salesPage->ai_output = $this->gemini->generateSalesPage($salesPage->toArray());
            } catch (Exception $e) {
                return response()->json([
                    'message' => 'AI re-generation failed.',
                    'error'   => $e->getMessage(),
                ], 502);
            }
        }

        $salesPage->save();

        return response()->json([
            'message' => 'Sales page updated successfully.',
            'data'    => $salesPage,
        ]);
    }

    /**
     * Delete a sales page.
     */
    public function destroy(Request $request, SalesPage $salesPage): JsonResponse
    {
        $this->authorizeOwner($request, $salesPage);

        $salesPage->delete();

        return response()->json([
            'message' => 'Sales page deleted successfully.',
        ]);
    }

    /**
     * Ensure the authenticated user owns the sales page.
     */
    private function authorizeOwner(Request $request, SalesPage $salesPage): void
    {
        if ($salesPage->user_id !== $request->user()->id) {
            abort(403, 'Unauthorized action.');
        }
    }
}
