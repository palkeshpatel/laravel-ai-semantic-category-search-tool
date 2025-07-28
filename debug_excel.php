<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use PhpOffice\PhpSpreadsheet\IOFactory;

$filePath = 'public/Lynx_Keyword_Enhanced_Services.xlsx';

if (!file_exists($filePath)) {
    echo "File not found: $filePath\n";
    exit(1);
}

$spreadsheet = IOFactory::load($filePath);
$worksheet = $spreadsheet->getActiveSheet();

echo "Excel File Analysis:\n";
echo "==================\n\n";

// Get the highest row and column numbers
$highestRow = $worksheet->getHighestRow();
$highestColumn = $worksheet->getHighestColumn();

echo "Total rows: $highestRow\n";
echo "Highest column: $highestColumn\n\n";

// Display headers
echo "Headers (Row 1):\n";
$headers = [];
foreach ($worksheet->getRowIterator(1, 1) as $row) {
    foreach ($row->getCellIterator() as $cell) {
        $headers[] = $cell->getValue();
        echo $cell->getValue() . " | ";
    }
    break;
}
echo "\n\n";

// Display first 10 rows of data
echo "First 10 rows of data:\n";
$rowCount = 0;
foreach ($worksheet->getRowIterator(2, min(11, $highestRow)) as $row) {
    $rowCount++;
    echo "Row $rowCount: ";
    foreach ($row->getCellIterator() as $cell) {
        echo $cell->getValue() . " | ";
    }
    echo "\n";
}

echo "\n\nTesting import with first few rows:\n";
echo "====================================\n";

// Test the import logic with first few rows
$rowCount = 0;
foreach ($worksheet->getRowIterator(2, min(5, $highestRow)) as $row) {
    $rowCount++;
    $rowData = [];
    foreach ($row->getCellIterator() as $cell) {
        $rowData[] = $cell->getValue();
    }

    echo "Processing Row $rowCount:\n";
    echo "  Main Category: '" . ($rowData[0] ?? '') . "'\n";
    echo "  Sub Category: '" . ($rowData[1] ?? '') . "'\n";
    echo "  Service: '" . ($rowData[2] ?? '') . "'\n";
    echo "  Keywords: '" . (substr($rowData[3] ?? '', 0, 50)) . "...'\n";
    echo "\n";
}
