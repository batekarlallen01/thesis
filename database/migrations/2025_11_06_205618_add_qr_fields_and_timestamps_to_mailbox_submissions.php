<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mailbox_submissions', function (Blueprint $table) {
            // QR Code fields
            $table->string('qr_token', 64)->nullable()->unique()->after('pin_code');
            $table->timestamp('qr_expires_at')->nullable()->after('qr_token');
            $table->string('qr_image_path')->nullable()->after('qr_expires_at');
            $table->boolean('has_entered_queue')->default(false)->after('qr_image_path');
            
            // Approval/disapproval timestamps
            $table->timestamp('submitted_at')->nullable()->after('status');
            $table->timestamp('approved_at')->nullable()->after('submitted_at');
            $table->timestamp('disapproved_at')->nullable()->after('approved_at');
            
            // Link to pre_registration (if approved)
            $table->unsignedBigInteger('pre_registration_id')->nullable()->after('has_entered_queue');
            
            // Age and PWD fields (needed for priority queue)
            $table->integer('age')->nullable()->after('full_name');
            $table->boolean('is_pwd')->default(false)->after('age');
            $table->string('pwd_id')->nullable()->after('is_pwd');
        });
    }

    public function down(): void
    {
        Schema::table('mailbox_submissions', function (Blueprint $table) {
            $table->dropColumn([
                'qr_token', 'qr_expires_at', 'qr_image_path', 'has_entered_queue',
                'submitted_at', 'approved_at', 'disapproved_at', 'pre_registration_id',
                'age', 'is_pwd', 'pwd_id'
            ]);
        });
    }
};