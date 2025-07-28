<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\EmbeddingService;

echo "Testing Free Embedding Service...\n\n";

$embeddingService = new EmbeddingService();

// Test embedding generation
$testText = "furniture assembly";
$embedding = $embeddingService->generateEmbedding($testText);

echo "✅ Embedding generated successfully!\n";
echo "Text: '$testText'\n";
echo "Embedding length: " . count($embedding) . "\n";
echo "First 5 values: " . implode(', ', array_slice($embedding, 0, 5)) . "\n\n";

// Test similarity calculation
$text1 = "furniture assembly";
$text2 = "furniture assembly service";
$text3 = "car repair";

$embedding1 = $embeddingService->generateEmbedding($text1);
$embedding2 = $embeddingService->generateEmbedding($text2);
$embedding3 = $embeddingService->generateEmbedding($text3);

$similarity1 = $embeddingService->calculateSimilarity($embedding1, $embedding2);
$similarity2 = $embeddingService->calculateSimilarity($embedding1, $embedding3);

echo "Similarity between '$text1' and '$text2': " . number_format($similarity1, 4) . "\n";
echo "Similarity between '$text1' and '$text3': " . number_format($similarity2, 4) . "\n";

if ($similarity1 > $similarity2) {
    echo "✅ Similarity working correctly - related terms have higher similarity!\n";
} else {
    echo "❌ Similarity not working as expected\n";
}
