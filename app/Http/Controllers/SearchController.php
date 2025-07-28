<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Services\EmbeddingService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class SearchController extends Controller
{
    protected $embeddingService;

    public function __construct(EmbeddingService $embeddingService)
    {
        $this->embeddingService = $embeddingService;
    }

    /**
     * Display the search form
     */
    public function index(): View
    {
        return view('search.index');
    }

    /**
     * Handle search request (AJAX)
     */
    public function search(Request $request): JsonResponse
    {
        $query = $request->input('query', '');
        $results = [];

        if (!empty($query)) {
            $results = $this->embeddingService->findSimilarServices($query, 10);
        }

        // Format results for JSON response
        $formattedResults = [];
        foreach ($results as $result) {
            $formattedResults[] = [
                'service' => [
                    'id' => $result['service']->id,
                    'name' => $result['service']->name,
                    'keywords' => $result['service']->keywords,
                    'main_category_name' => $result['service']->getMainCategoryName(),
                    'sub_category_name' => $result['service']->getSubCategoryName(),
                    'full_path' => $result['service']->getFullPath()
                ],
                'similarity' => $result['similarity']
            ];
        }

        return response()->json([
            'query' => $query,
            'results' => $formattedResults,
            'total' => count($formattedResults)
        ]);
    }
}
