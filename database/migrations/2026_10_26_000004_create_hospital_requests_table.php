<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hospital_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospital_id')->constrained()->onDelete('restrict');
            $table->date('request_date')->index();
            $table->string('blood_type', 2);
            $table->string('rh_factor', 1);
            $table->unsignedInteger('units_requested');
            $table->string('status', 20)->default('Pending')->index();
            $table->timestamps();
            $table->index(['blood_type', 'rh_factor', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hospital_requests');
    }
};
