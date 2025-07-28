<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Imports\HierarchicalCategoriesImport;
use Maatwebsite\Excel\Facades\Excel;

echo "Testing Hierarchical Import...\n";
echo "============================\n\n";

$filePath = 'public/Lynx_Keyword_Enhanced_Services.xlsx';

if (!file_exists($filePath)) {
    echo "File not found: $filePath\n";
    exit(1);
}

try {
    $import = new HierarchicalCategoriesImport();

    // Test with just first 5 rows
    $collection = Excel::toCollection($import, $filePath)->first();

    echo "Collection has " . $collection->count() . " rows\n\n";

    // Display first few rows
    foreach ($collection->take(5) as $index => $row) {
        echo "Row " . ($index + 1) . ":\n";
        echo "  Keys: " . implode(', ', array_keys($row->toArray())) . "\n";
        echo "  Main Category: '" . ($row['Main Category'] ?? 'NOT FOUND') . "'\n";
        echo "  Sub Category: '" . ($row['Sub Category'] ?? 'NOT FOUND') . "'\n";
        echo "  Service: '" . ($row['Service'] ?? 'NOT FOUND') . "'\n";
        echo "  Keywords: '" . (substr($row['Keywords'] ?? 'NOT FOUND', 0, 30)) . "...'\n";
        echo "\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
