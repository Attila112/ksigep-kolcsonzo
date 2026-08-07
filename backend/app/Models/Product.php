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
        'sku',
        'inventory_prefix',
        'description',
        'image_path',
        'price_per_day',
        'deposit',
        'active',
        'battery_system_id',
        'required_batteries',
        'required_chargers',
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
    public function batterySystem(): BelongsTo
    {
        return $this->belongsTo(BatterySystem::class);
    }
    protected function casts(): array
    {
        return [
            'price_per_day' => 'decimal:2',
            'deposit' => 'decimal:2',
            'active' => 'boolean',
            'required_batteries' => 'integer',
            'required_chargers' => 'integer',
        ];
    }
}
