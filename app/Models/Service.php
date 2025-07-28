<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Service extends Model
{
    protected $fillable = [
        'name',
        'keywords',
        'embedding',
        'sub_category_id'
    ];

    protected $casts = [
        'embedding' => 'array'
    ];

    /**
     * Get the sub category that owns this service
     */
    public function subCategory(): BelongsTo
    {
        return $this->belongsTo(SubCategory::class);
    }

    /**
     * Get the main category through sub category
     */
    public function mainCategory()
    {
        return $this->subCategory->mainCategory;
    }

    /**
     * Get the embedding as an array
     */
    public function getEmbeddingArray(): ?array
    {
        return $this->embedding;
    }

    /**
     * Set the embedding from an array
     */
    public function setEmbeddingArray(array $embedding): void
    {
        $this->embedding = $embedding;
        $this->save();
    }

    /**
     * Check if service has embedding
     */
    public function hasEmbedding(): bool
    {
        return !empty($this->embedding);
    }

    /**
     * Get full hierarchical path
     */
    public function getFullPath(): string
    {
        return $this->subCategory->mainCategory->name . ' → ' . $this->subCategory->name . ' → ' . $this->name;
    }

    /**
     * Get main category name safely
     */
    public function getMainCategoryName(): string
    {
        return $this->subCategory->mainCategory->name ?? 'Unknown';
    }

    /**
     * Get sub category name safely
     */
    public function getSubCategoryName(): string
    {
        return $this->subCategory->name ?? 'Unknown';
    }
}
