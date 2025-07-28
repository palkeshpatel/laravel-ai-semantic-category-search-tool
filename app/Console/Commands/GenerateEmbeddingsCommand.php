<?php

namespace App\Console\Commands;

use App\Services\EmbeddingService;
use Illuminate\Console\Command;

class GenerateEmbeddingsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:embeddings';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate embeddings for all categories';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting embedding generation...');

        $embeddingService = new EmbeddingService();
        $processed = $embeddingService->generateEmbeddingsForCategories();

        $this->info("Successfully generated embeddings for {$processed} categories!");

        return 0;
    }
}
