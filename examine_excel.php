<?php

require_once 'vendor/autoload.php';

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

// Display first few rows
echo "First 5 rows of data:\n";
$rowCount = 0;
foreach ($worksheet->getRowIterator(2, min(6, $highestRow)) as $row) {
    $rowCount++;
    echo "Row $rowCount: ";
    foreach ($row->getCellIterator() as $cell) {
        echo $cell->getValue() . " | ";
    }
    echo "\n";
}
