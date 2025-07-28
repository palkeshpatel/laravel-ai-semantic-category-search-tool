<?php

namespace App\Imports;

use App\Models\Category;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class CategoriesImport implements ToCollection, WithHeadingRow, WithValidation
{
    /**
     * @param Collection $collection
     */
    public function collection(Collection $collection)
    {
        foreach ($collection as $row) {
            // Process each column that contains category data
            foreach ($row as $columnName => $value) {
                if ($value && !empty(trim($value))) {
                    $categoryText = trim($value);

                    // Split by commas and process each part
                    $categories = array_map('trim', explode(',', $categoryText));

                    foreach ($categories as $categoryName) {
                        if (!empty($categoryName) && strlen($categoryName) > 3) {
                            Category::updateOrCreate(
                                ['name' => $categoryName],
                                ['name' => $categoryName]
                            );
                        }
                    }
                }
            }
        }
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            '*' => 'nullable|string', // Allow any column
        ];
    }

    /**
     * @return array
     */
    public function customValidationMessages(): array
    {
        return [
            '*.string' => 'All values must be strings.',
        ];
    }
}
