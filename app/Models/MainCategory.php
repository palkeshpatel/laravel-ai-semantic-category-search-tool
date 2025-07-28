<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MainCategory extends Model
{
    protected $fillable = [
        'name'
    ];

    /**
     * Get the sub categories for this main category
     */
    public function subCategories(): HasMany
    {
        return $this->hasMany(SubCategory::class);
    }

    /**
     * Get all services through sub categories
     */
    public function services()
    {
        return $this->hasManyThrough(Service::class, SubCategory::class);
    }
}
