<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expedient_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expedient_id')->constrained();
            $table->foreignId('user_id')->constrained();
            $table->string('movement_type');
            $table->foreignId('from_location_id')->nullable()->constrained('archive_locations')->nullOnDelete();
            $table->foreignId('to_location_id')->nullable()->constrained('archive_locations')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['expedient_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expedient_movements');
    }
};
