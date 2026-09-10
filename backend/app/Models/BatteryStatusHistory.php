<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BatteryStatusHistory extends Model
{
    protected $fillable = [
        'battery_item_id',
        'changed_by_user_id',
        'from_status',
        'to_status',
        'note',
    ];

    public function batteryItem(): BelongsTo
    {
        return $this->belongsTo(BatteryItem::class);
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'changed_by_user_id'
        );
    }
}