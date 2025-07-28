<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use OpenAI\Laravel\Facades\OpenAI;

echo "Testing OpenAI API connection...\n";
echo "API Key from env(): " . (env('OPENAI_API_KEY') ? 'Set' : 'Not set') . "\n";
echo "API Key from config: " . (config('openai.api_key') ? 'Set' : 'Not set') . "\n";
echo "API Key length: " . strlen(env('OPENAI_API_KEY') ?? '') . "\n\n";

try {
    $response = OpenAI::embeddings()->create([
        'model' => 'text-embedding-3-small',
        'input' => 'test',
    ]);

    echo "✅ OpenAI API is working!\n";
    echo "Embedding length: " . count($response->embeddings[0]->embedding) . "\n";
} catch (\Exception $e) {
    echo "❌ OpenAI API failed: " . $e->getMessage() . "\n";
}