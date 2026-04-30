<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\SalesPage\StoreSalesPageRequest;
use App\Http\Requests\Api\SalesPage\UpdateSalesPageRequest;
use App\Http\Resources\SalesPageResource;
use App\Models\SalesPage;
use App\Services\SalesPageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Exception;

class SalesPageController extends Controller
{
    public function __construct(protected SalesPageService $service) {}

    /**
     * List all sales pages belonging to the authenticated user.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $salesPages = $request->user()
            ->salesPages()
            ->latest()
            ->get();

        return SalesPageResource::collection($salesPages);
    }

    /**
     * Create a new sales page and generate AI copy via Gemini.
     */
    public function store(StoreSalesPageRequest $request): JsonResponse
    {
        try {
            $salesPage = $this->service->createForUser($request->user(), $request->validated());

            return (new SalesPageResource($salesPage))
                ->additional(['message' => 'Sales page created successfully.'])
                ->response()
                ->setStatusCode(201);
        } catch (Exception $e) {
            \Log::error('Sales Page Creation Failed', [
                'user_id' => $request->user()->id,
                'error'   => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'AI generation failed.',
                'error'   => $e->getMessage(),
            ], 502);
        }
    }

    /**
     * Show a single sales page.
     */
    public function show(SalesPage $salesPage): SalesPageResource
    {
        $this->authorize('view', $salesPage);

        return new SalesPageResource($salesPage);
    }

    /**
     * Update product details and AI copy.
     */
    public function update(UpdateSalesPageRequest $request, SalesPage $salesPage): JsonResponse
    {
        try {
            $updatedPage = $this->service->updatePage($salesPage, $request->validated());

            return (new SalesPageResource($updatedPage))
                ->additional(['message' => 'Sales page updated successfully.'])
                ->response();
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Update failed.',
                'error'   => $e->getMessage(),
            ], 502);
        }
    }

    /**
     * Generate AI copy preview without saving.
     */
    public function generatePreview(Request $request, SalesPage $salesPage): JsonResponse
    {
        $this->authorize('update', $salesPage);

        try {
            $aiOutput = $this->service->generatePreview($salesPage, $request->all());
            
            return response()->json([
                'message' => 'AI preview generated successfully.',
                'data'    => $aiOutput,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'AI preview generation failed.',
                'error'   => $e->getMessage(),
            ], 502);
        }
    }

    /**
     * Delete a sales page.
     */
    public function destroy(SalesPage $salesPage): JsonResponse
    {
        $this->authorize('delete', $salesPage);

        $salesPage->delete();

        return response()->json([
            'message' => 'Sales page deleted successfully.',
        ]);
    }
}
