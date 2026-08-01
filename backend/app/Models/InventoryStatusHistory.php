<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryStatusHistory extends Model
{
    protected $fillable = [
        'inventory_item_id',
        'changed_by_user_id',
        'from_status',
        'to_status',
        'note',
    ];

    /**
     * Returns the physical machine whose status changed.
     */
    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class);
    }

    /**
     * Returns the admin who changed the status.
     *
     * This can be null when the status change was performed
     * automatically by the system.
     */
    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'changed_by_user_id'
        );
    }
}