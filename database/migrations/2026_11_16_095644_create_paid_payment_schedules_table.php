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
        Schema::create('paid_payment_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->references('id')->on('payments');
            $table->foreignId('payment_schedule_id')
                    ->references('id')->on('payment_schedules');
            $table->decimal('amount', 10,2);
            $table->decimal('principal', 10,2);
            $table->decimal('interest', 10,2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paid_payment_schedules');
    }
};
