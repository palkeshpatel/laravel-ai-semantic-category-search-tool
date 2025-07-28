<?php

namespace App\Imports;

use App\Models\MainCategory;
use App\Models\SubCategory;
use App\Models\Service;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class HierarchicalCategoriesImport implements ToCollection, WithHeadingRow, WithValidation
{
    /**
     * @param Collection $collection
     */
    public function collection(Collection $collection)
    {
        $currentMainCategory = null;
        $currentSubCategory = null;

        foreach ($collection as $row) {
            // Skip completely empty rows
            if (empty($row['main_category']) && empty($row['sub_category']) && empty($row['service'])) {
                continue;
            }

            // Handle Main Category
            $mainCategoryName = trim($row['main_category'] ?? '');
            if (!empty($mainCategoryName)) {
                $currentMainCategory = MainCategory::firstOrCreate(
                    ['name' => $mainCategoryName],
                    ['name' => $mainCategoryName]
                );
            }

            // Handle Sub Category
            $subCategoryName = trim($row['sub_category'] ?? '');
            if (!empty($subCategoryName) && $currentMainCategory) {
                $currentSubCategory = SubCategory::firstOrCreate(
                    [
                        'name' => $subCategoryName,
                        'main_category_id' => $currentMainCategory->id
                    ],
                    [
                        'name' => $subCategoryName,
                        'main_category_id' => $currentMainCategory->id
                    ]
                );
            }

            // Handle Service (only if we have both main and sub category)
            $serviceName = trim($row['service'] ?? '');
            $keywords = trim($row['keywords'] ?? '');

            if (!empty($serviceName) && $currentSubCategory) {
                $service = Service::firstOrCreate(
                    [
                        'name' => $serviceName,
                        'sub_category_id' => $currentSubCategory->id
                    ],
                    [
                        'name' => $serviceName,
                        'keywords' => $keywords,
                        'sub_category_id' => $currentSubCategory->id
                    ]
                );

                // Update keywords if they exist
                if (!empty($keywords)) {
                    $service->update(['keywords' => $keywords]);
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
            'main_category' => 'nullable|string',
            'sub_category' => 'nullable|string',
            'service' => 'nullable|string',
            'keywords' => 'nullable|string',
        ];
    }

    /**
     * @return array
     */
    public function customValidationMessages(): array
    {
        return [
            'main_category.string' => 'Main Category must be a string.',
            'sub_category.string' => 'Sub Category must be a string.',
            'service.string' => 'Service must be a string.',
            'keywords.string' => 'Keywords must be a string.',
        ];
    }
}
