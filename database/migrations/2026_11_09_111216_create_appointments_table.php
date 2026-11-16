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
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('service');
            $table->date('appointment_date');
            $table->decimal('fee', 10, 2);
            $table->string('payment_method', 20);
            $table->string('payment_status', 20)->default('Pending');
            $table->string('status', 20)->default('Pending');
            $table->timestamps();

            $table->index(['user_id', 'appointment_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
