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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('customer_phone');

            $table->date('start_date');
            $table->date('end_date');

            $table->enum('pickup_type', [
                'SELF_PICKUP',
                'DELIVERY',
            ]);

            $table->dateTime('planned_pickup_at')->nullable();

            $table->string('delivery_postal_code')->nullable();
            $table->string('delivery_city')->nullable();
            $table->string('delivery_street')->nullable();
            $table->string('delivery_house_number')->nullable();
            $table->decimal('delivery_latitude', 10, 7)->nullable();
            $table->decimal('delivery_longitude', 10, 7)->nullable();
            $table->decimal('delivery_distance_km', 6, 2)->nullable();

            $table->enum('status', [
                'PENDING',
                'CONFIRMED',
                'REJECTED',
                'CANCELLED',
                'ACTIVE',
                'COMPLETED',
            ])->default('PENDING');

            $table->text('customer_note')->nullable();
            $table->text('admin_note')->nullable();

            $table->timestamps();

            $table->index(['start_date', 'end_date']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
