<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('census_skips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('archive_location_id')->constrained('archive_locations')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('reason')->default('Carpeta física no localizada en lote');
            $table->string('status', 30)->default('deferred'); // deferred, resolved, incident
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index(['archive_location_id', 'status']);
            $table->index(['employee_id', 'status']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('census_skips');
    }
};
