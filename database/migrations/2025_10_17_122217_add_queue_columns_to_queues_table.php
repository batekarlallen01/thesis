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
        Schema::table('queues', function (Blueprint $table) {
            // Add all missing columns to queues table
            $table->string('queue_number')->after('id');
            $table->string('full_name')->nullable();
            $table->string('email')->nullable();
            $table->string('contact')->nullable();
            $table->date('birthdate')->nullable();
            $table->integer('age')->nullable();
            $table->string('service_type')->nullable();
            $table->boolean('is_pwd')->default(false);
            $table->string('pwd_id')->nullable();
            $table->string('senior_id')->nullable();
            $table->enum('priority_type', ['Regular', 'PWD', 'Senior'])->default('Regular');
            $table->enum('entry_type', ['direct', 'pre_registration'])->default('direct');
            $table->enum('status', ['waiting', 'serving', 'completed', 'cancelled', 'no_show'])->default('waiting');
            $table->timestamp('queue_entered_at')->nullable();
            $table->timestamp('served_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->unsignedBigInteger('pre_registration_id')->nullable();
            $table->json('form_data')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('queues', function (Blueprint $table) {
            $table->dropColumn([
                'queue_number',
                'full_name',
                'email',
                'contact',
                'birthdate',
                'age',
                'service_type',
                'is_pwd',
                'pwd_id',
                'senior_id',
                'priority_type',
                'entry_type',
                'status',
                'queue_entered_at',
                'served_at',
                'completed_at',
                'pre_registration_id',
                'form_data'
            ]);
        });
    }
};