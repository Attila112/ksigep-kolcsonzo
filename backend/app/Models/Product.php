<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Product extends Model
{
    protected $fillable = [
        'category_id',
        'name',
        'description',
        'price_per_day',
        'deposit',
        'active',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function inventoryItems(): HasMany
    {
        return $this->hasMany(InventoryItem::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }
    /**
     * Returns the work types for which this product is recommended.
     */
    public function workTypes(): BelongsToMany
    {
        return $this->belongsToMany(WorkType::class)
            ->withTimestamps();
    }
}
