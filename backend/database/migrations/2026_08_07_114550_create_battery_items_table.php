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
        Schema::create('battery_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('battery_system_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('inventory_code')->unique();

            $table->string('type');

            $table->string('serial_number')
                ->nullable()
                ->unique();

            $table->string('status')
                ->default('AVAILABLE');

            $table->text('admin_note')
                ->nullable();

            $table->timestamps();

            $table->index([
                'battery_system_id',
                'type',
                'status',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('battery_items');
    }
};
