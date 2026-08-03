<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class WorkType extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon_key',
        'active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Returns the products recommended for this type of work.
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class)
            ->withTimestamps();
    }
    /**
     * Uses the slug instead of the numeric ID in public URLs.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
