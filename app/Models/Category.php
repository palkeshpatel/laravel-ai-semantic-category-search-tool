<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = [
        'name',
        'embedding'
    ];

    protected $casts = [
        'embedding' => 'array'
    ];

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
     * Check if category has embedding
     */
    public function hasEmbedding(): bool
    {
        return !empty($this->embedding);
    }
}
