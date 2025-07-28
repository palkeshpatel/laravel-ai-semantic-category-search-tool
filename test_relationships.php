<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Service;

echo "Testing Hierarchical Relationships...\n";
echo "==================================\n\n";

// Get a few services with relationships loaded
$services = Service::with(['subCategory.mainCategory'])->take(5)->get();

foreach ($services as $service) {
    echo "Service: " . $service->name . "\n";
    echo "  Sub Category: " . $service->subCategory->name . "\n";
    echo "  Main Category: " . $service->subCategory->mainCategory->name . "\n";
    echo "  Full Path: " . $service->getFullPath() . "\n";
    echo "\n";
}

echo "Total Services: " . Service::count() . "\n";
echo "Services with embeddings: " . Service::whereNotNull('embedding')->count() . "\n";
