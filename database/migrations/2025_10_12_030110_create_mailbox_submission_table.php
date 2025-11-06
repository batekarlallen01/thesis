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
        Schema::create('mailbox_submissions', function (Blueprint $table) {
            $table->id();
            $table->string('service_type');
            $table->string('applicant_type');
            $table->integer('number_of_copies');
            $table->string('full_name');
            $table->string('pin_land')->nullable();
            $table->string('pin_building')->nullable();
            $table->string('pin_machinery')->nullable();
            $table->text('purpose');
            $table->string('govt_id_type');
            $table->string('govt_id_number');
            $table->string('issued_at')->nullable();
            $table->date('issued_on')->nullable();
            $table->text('address');
            $table->string('email');
            $table->string('pin_code', 6)->unique(); // 6-digit PIN for IoT mailbox
            $table->enum('status', ['pending', 'processing', 'completed', 'collected'])->default('pending');
            $table->timestamp('collected_at')->nullable();
            $table->timestamps();
            
            // Indexes for faster queries
            $table->index('pin_code');
            $table->index('email');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mailbox_submissions');
    }
};