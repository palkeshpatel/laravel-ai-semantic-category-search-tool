<?php

namespace App\Services;

use App\Models\Category;
use OpenAI\Laravel\Facades\OpenAI;
use Illuminate\Support\Facades\Log;

class EmbeddingService
{
    /**
     * Generate embedding for a text using OpenAI
     */
    public function generateEmbedding(string $text): ?array
    {
        try {
            $response = OpenAI::embeddings()->create([
                'model' => 'text-embedding-3-small',
                'input' => $text,
            ]);

            return $response->embeddings[0]->embedding;
        } catch (\Exception $e) {
            Log::error('Failed to generate embedding: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Generate embeddings for all categories without embeddings
     */
    public function generateEmbeddingsForCategories(): int
    {
        $categories = Category::whereNull('embedding')->get();
        $processed = 0;

        foreach ($categories as $category) {
            $embedding = $this->generateEmbedding($category->name);

            if ($embedding) {
                $category->setEmbeddingArray($embedding);
                $processed++;
            }

            // Add a small delay to avoid rate limiting
            usleep(100000); // 0.1 second
        }

        return $processed;
    }

    /**
     * Calculate cosine similarity between two vectors
     */
    public function calculateSimilarity(array $vector1, array $vector2): float
    {
        $dotProduct = 0;
        $magnitude1 = 0;
        $magnitude2 = 0;

        foreach ($vector1 as $i => $value1) {
            $value2 = $vector2[$i] ?? 0;
            $dotProduct += $value1 * $value2;
            $magnitude1 += $value1 * $value1;
            $magnitude2 += $value2 * $value2;
        }

        $magnitude1 = sqrt($magnitude1);
        $magnitude2 = sqrt($magnitude2);

        if ($magnitude1 == 0 || $magnitude2 == 0) {
            return 0;
        }

        return $dotProduct / ($magnitude1 * $magnitude2);
    }

    /**
     * Find similar categories based on query
     */
    public function findSimilarCategories(string $query, int $limit = 5): array
    {
        $queryEmbedding = $this->generateEmbedding($query);

        if (!$queryEmbedding) {
            return [];
        }

        $categories = Category::whereNotNull('embedding')->get();
        $results = [];

        foreach ($categories as $category) {
            $similarity = $this->calculateSimilarity($queryEmbedding, $category->getEmbeddingArray());

            $results[] = [
                'category' => $category,
                'similarity' => $similarity
            ];
        }

        // Sort by similarity (descending)
        usort($results, function ($a, $b) {
            return $b['similarity'] <=> $a['similarity'];
        });

        return array_slice($results, 0, $limit);
    }
}
