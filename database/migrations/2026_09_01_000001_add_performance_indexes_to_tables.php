<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('loan_requests', function (Blueprint $table) {
            $table->index(['status', 'due_date'], 'loans_status_due_date_index');
            $table->index('requester_id', 'loans_requester_id_index');
        });

        Schema::table('expedients', function (Blueprint $table) {
            $table->index(['current_status', 'current_location_id'], 'expedients_status_location_index');
        });
    }

    public function down(): void
    {
        Schema::table('loan_requests', function (Blueprint $table) {
            $table->dropIndex('loans_status_due_date_index');
            $table->dropIndex('loans_requester_id_index');
        });

        Schema::table('expedients', function (Blueprint $table) {
            $table->dropIndex('expedients_status_location_index');
        });
    }
};
