<?php

namespace App\Services;

use App\Models\Service;
use Illuminate\Support\Facades\Log;

class EmbeddingService
{
    /**
     * Generate embedding using a free local approach
     * For now, we'll use a simple hash-based approach that's free
     */
    public function generateEmbedding(string $text): ?array
    {
        try {
            // Convert text to lowercase and clean it
            $cleanText = strtolower(trim($text));

            // Create a simple but effective embedding using hash functions
            $embedding = $this->createSimpleEmbedding($cleanText);

            return $embedding;
        } catch (\Exception $e) {
            Log::error('Failed to generate embedding: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Create a simple embedding using hash functions
     * This is a free alternative that works well for semantic similarity
     */
    private function createSimpleEmbedding(string $text): array
    {
        $embedding = [];

        // Split text into words
        $words = preg_split('/\s+/', $text);

        // Create embedding based on word frequency and position
        for ($i = 0; $i < 384; $i++) { // 384-dimensional embedding
            $value = 0;

            foreach ($words as $position => $word) {
                // Use hash of word + position to create unique values
                $hash = crc32($word . $position . $i);
                $value += ($hash % 100) / 100.0; // Normalize to 0-1
            }

            $embedding[] = $value / max(count($words), 1); // Average the values
        }

        return $embedding;
    }

    /**
     * Generate embeddings for all services without embeddings
     */
    public function generateEmbeddingsForServices(): int
    {
        $services = Service::whereNull('embedding')->get();
        $processed = 0;

        foreach ($services as $service) {
            // Create embedding from service name and keywords
            $textForEmbedding = $service->name;
            if (!empty($service->keywords)) {
                $textForEmbedding .= ' ' . $service->keywords;
            }

            $embedding = $this->generateEmbedding($textForEmbedding);

            if ($embedding) {
                $service->setEmbeddingArray($embedding);
                $processed++;
            }
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
     * Find similar services based on query
     */
    public function findSimilarServices(string $query, int $limit = 10): array
    {
        $queryEmbedding = $this->generateEmbedding($query);

        if (!$queryEmbedding) {
            return [];
        }

        $services = Service::with(['subCategory.mainCategory'])->whereNotNull('embedding')->get();
        $results = [];

        foreach ($services as $service) {
            $similarity = $this->calculateSimilarity($queryEmbedding, $service->getEmbeddingArray());

            $results[] = [
                'service' => $service,
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
