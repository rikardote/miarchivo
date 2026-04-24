<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loan_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expedient_id')->constrained();
            $table->foreignId('requester_id')->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->string('status')->default('pending');
            $table->timestamp('requested_at');
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('reserved_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('returned_at')->nullable();
            $table->date('due_date')->nullable();
            $table->text('observations')->nullable();
            $table->text('return_notes')->nullable();
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loan_requests');
    }
};
