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
        Schema::table('pre_registrations', function (Blueprint $table) {
            $table->dropColumn(['govt_id_type', 'govt_id_number', 'issued_at', 'issued_on']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pre_registrations', function (Blueprint $table) {
            $table->string('govt_id_type')->nullable();
            $table->string('govt_id_number')->nullable();
            $table->string('issued_at')->nullable();
            $table->date('issued_on')->nullable();
        });
    }
};