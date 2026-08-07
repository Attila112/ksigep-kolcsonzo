<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BatterySystem extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'manufacturer',
        'voltage',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'voltage' => 'decimal:2',
            'active' => 'boolean',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(BatteryItem::class);
    }
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
