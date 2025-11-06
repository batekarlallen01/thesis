<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kiosk_entries', function (Blueprint $table) {
            $table->id();
            
            // Personal Information
            $table->string('full_name');
            $table->integer('age')->nullable();
            
            // PWD Information
            $table->boolean('is_pwd')->default(false);
            $table->string('pwd_id')->nullable();
            
            // Service Information
            $table->enum('applicant_type', ['owner', 'representative']);
            $table->enum('service_type', [
                'tax_declaration',
                'no_improvement',
                'property_holdings',
                'non_property_holdings'
            ]);
            $table->integer('number_of_copies');
            
            // Property Information
            $table->string('pin_land')->nullable();
            $table->string('pin_building')->nullable();
            $table->string('pin_machinery')->nullable();
            
            // Additional Information
            $table->text('purpose');
            $table->text('address');
            
            // Government ID
            $table->string('govt_id_type');
            $table->string('govt_id_number');
            $table->string('issued_at')->nullable();
            $table->date('issued_on')->nullable();
            
            // Queue Status
            $table->enum('status', ['pending', 'in_queue', 'completed', 'cancelled'])->default('in_queue');
            $table->unsignedBigInteger('queue_id')->nullable();
            
            // Priority Information (computed)
            $table->enum('priority_type', ['Regular', 'PWD', 'Senior'])->default('Regular');
            
            $table->timestamps();
            
            // Foreign key to queue table
            $table->foreign('queue_id')->references('id')->on('queues')->onDelete('set null');
            
            // Indexes
            $table->index(['status', 'created_at']);
            $table->index('queue_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kiosk_entries');
    }
};