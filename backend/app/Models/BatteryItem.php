<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BatteryItem extends Model
{
    use HasFactory;
    public const TYPE_BATTERY = 'BATTERY';
    public const TYPE_CHARGER = 'CHARGER';

    public const STATUS_AVAILABLE = 'AVAILABLE';
    public const STATUS_RENTED = 'RENTED';
    public const STATUS_INSPECTION = 'INSPECTION';
    public const STATUS_MAINTENANCE = 'MAINTENANCE';
    public const STATUS_DAMAGED = 'DAMAGED';
    public const STATUS_INACTIVE = 'INACTIVE';

    protected $fillable = [
        'battery_system_id',
        'inventory_code',
        'type',
        'serial_number',
        'status',
        'admin_note',
    ];

    public function batterySystem(): BelongsTo
    {
        return $this->belongsTo(BatterySystem::class);
    }
}