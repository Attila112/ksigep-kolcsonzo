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
        Schema::create('inventory_status_histories', function (Blueprint $table) {
            $table->id();

            $table->foreignId('inventory_item_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('changed_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('from_status');
            $table->string('to_status');
            $table->text('note')->nullable();

            $table->timestamps();

            $table->index([
                'inventory_item_id',
                'created_at',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_status_histories');
    }
};
