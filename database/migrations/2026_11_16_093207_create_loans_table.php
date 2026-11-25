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
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('debtor_id')->references('id')->on('debtors');
            $table->string('type');//monthly, weekly, daily
            $table->decimal('principal', 10,2);
            $table->integer('terms');
            $table->float('interest_rate');
            $table->date('start_date');
            $table->date('end_date');
            $table->string('status'); //applied, approved, closed
            $table->date('release_date');
            $table->string('remarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};
