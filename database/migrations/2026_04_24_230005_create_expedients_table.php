<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expedients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained();
            $table->string('expedient_code')->unique();
            $table->unsignedSmallInteger('volume_number')->default(1);
            $table->string('current_status')->default('available');
            $table->foreignId('current_location_id')->nullable()->constrained('archive_locations')->nullOnDelete();
            $table->foreignId('current_holder_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('qr_code')->nullable();
            $table->string('barcode')->nullable();
            $table->date('opened_at')->nullable();
            $table->date('closed_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('current_status');
            $table->index('expedient_code');
            $table->unique(['employee_id', 'volume_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expedients');
    }
};
