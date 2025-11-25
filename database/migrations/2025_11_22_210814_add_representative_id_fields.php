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
        // Add representative ID fields to kiosk_entries table
        Schema::table('kiosk_entries', function (Blueprint $table) {
            $table->string('rep_govt_id_type', 100)->nullable()->after('issued_on');
            $table->string('rep_govt_id_number', 100)->nullable()->after('rep_govt_id_type');
            $table->string('rep_issued_at', 100)->nullable()->after('rep_govt_id_number');
            $table->date('rep_issued_on')->nullable()->after('rep_issued_at');
        });

        // Add representative ID fields to queues table
        Schema::table('queues', function (Blueprint $table) {
            $table->string('rep_govt_id_type', 100)->nullable()->after('issued_on');
            $table->string('rep_govt_id_number', 100)->nullable()->after('rep_govt_id_type');
            $table->string('rep_issued_at', 100)->nullable()->after('rep_govt_id_number');
            $table->date('rep_issued_on')->nullable()->after('rep_issued_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove representative ID fields from kiosk_entries table
        Schema::table('kiosk_entries', function (Blueprint $table) {
            $table->dropColumn([
                'rep_govt_id_type',
                'rep_govt_id_number',
                'rep_issued_at',
                'rep_issued_on'
            ]);
        });

        // Remove representative ID fields from queues table
        Schema::table('queues', function (Blueprint $table) {
            $table->dropColumn([
                'rep_govt_id_type',
                'rep_govt_id_number',
                'rep_issued_at',
                'rep_issued_on'
            ]);
        });
    }
};