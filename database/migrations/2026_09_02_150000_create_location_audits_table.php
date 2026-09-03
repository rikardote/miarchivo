<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('location_audits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('archive_location_id')->constrained('archive_locations')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedInteger('expected_count')->default(0);
            $table->unsignedInteger('scanned_count')->default(0);
            $table->unsignedInteger('correct_count')->default(0);
            $table->unsignedInteger('missing_count')->default(0);
            $table->unsignedInteger('misplaced_count')->default(0);
            $table->json('details')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('archive_location_id');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('location_audits');
    }
};
