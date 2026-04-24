<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('external_api_id')->nullable()->unique();
            $table->string('employee_number')->nullable()->unique();
            $table->string('rfc', 13)->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('position')->nullable();
            $table->string('work_center')->nullable();
            $table->string('city')->nullable();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('employment_status', ['active', 'inactive', 'terminated'])->default('active');
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('rfc');
            $table->index(['last_name', 'first_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
