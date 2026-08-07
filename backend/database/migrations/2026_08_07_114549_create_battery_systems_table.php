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
        Schema::create('battery_systems', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('manufacturer');
            $table->decimal('voltage', 5, 2)->nullable();
            $table->boolean('active')->default(true);

            $table->timestamps();

            $table->unique([
                'manufacturer',
                'name',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('battery_systems');
    }
};
