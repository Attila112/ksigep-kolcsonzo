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
        Schema::table('products', function (Blueprint $table) {

            $table->foreignId('battery_system_id')
                ->nullable()
                ->after('inventory_prefix')
                ->constrained()
                ->nullOnDelete();

            $table->unsignedTinyInteger('required_batteries')
                ->default(0)
                ->after('battery_system_id');

            $table->unsignedTinyInteger('required_chargers')
                ->default(0)
                ->after('required_batteries');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {

            $table->dropConstrainedForeignId('battery_system_id');

            $table->dropColumn([
                'required_batteries',
                'required_chargers',
            ]);
        });
    }
};
