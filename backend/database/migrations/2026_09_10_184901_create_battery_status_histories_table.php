<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'battery_status_histories',
            function (Blueprint $table) {
                $table->id();

                $table->foreignId('battery_item_id')
                    ->constrained()
                    ->cascadeOnDelete();

                $table->foreignId('changed_by_user_id')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table->string('from_status');
                $table->string('to_status');

                $table->text('note')
                    ->nullable();

                $table->timestamps();

                $table->index([
                    'battery_item_id',
                    'created_at',
                ]);
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'battery_status_histories'
        );
    }
};