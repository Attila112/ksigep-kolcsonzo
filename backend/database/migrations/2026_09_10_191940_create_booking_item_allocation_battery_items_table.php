<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'booking_item_allocation_battery_items',
            function (Blueprint $table) {
                $table->id();

                $table->foreignId(
                    'booking_item_allocation_id'
                )
                    ->constrained(
                        'booking_item_allocations'
                    )
                    ->cascadeOnDelete();

                $table->foreignId(
                    'battery_item_id'
                )
                    ->constrained(
                        'battery_items'
                    )
                    ->restrictOnDelete();

                $table->timestamp(
                    'assigned_at'
                );

                $table->timestamp(
                    'returned_at'
                )->nullable();

                $table->timestamps();

                $table->unique(
                    [
                        'booking_item_allocation_id',
                        'battery_item_id',
                    ],
                    'booking_allocation_battery_unique'
                );

                $table->index(
                    [
                        'battery_item_id',
                        'returned_at',
                    ],
                    'battery_allocation_return_index'
                );
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'booking_item_allocation_battery_items'
        );
    }
};