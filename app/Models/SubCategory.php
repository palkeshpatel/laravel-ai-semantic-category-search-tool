<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubCategory extends Model
{
    protected $fillable = [
        'name',
        'main_category_id'
    ];

    /**
     * Get the main category that owns this sub category
     */
    public function mainCategory(): BelongsTo
    {
        return $this->belongsTo(MainCategory::class);
    }

    /**
     * Get the services for this sub category
     */
    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }
}
