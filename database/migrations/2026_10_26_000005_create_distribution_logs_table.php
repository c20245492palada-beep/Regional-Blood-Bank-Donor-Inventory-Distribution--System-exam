<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('distribution_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blood_bag_id')->unique()->constrained()->onDelete('restrict');
            $table->foreignId('hospital_request_id')->constrained()->onDelete('restrict');
            $table->unsignedInteger('units_issued')->default(1);
            $table->decimal('processing_fee', 10, 2)->default(0);
            $table->string('status', 20)->default('Completed')->index();
            $table->timestamp('issued_at')->index();
            $table->timestamps();
            $table->index(['hospital_request_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('distribution_logs');
    }
};
