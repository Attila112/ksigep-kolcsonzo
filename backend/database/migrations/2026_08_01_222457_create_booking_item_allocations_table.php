<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('booking_item_allocations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('booking_item_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('inventory_item_id')
                ->constrained()
                ->restrictOnDelete();

            $table->timestamp('assigned_at');
            $table->timestamp('returned_at')->nullable();

            $table->timestamps();

            $table->unique(
                ['booking_item_id', 'inventory_item_id'],
                'booking_item_inventory_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_item_allocations');
    }
};
