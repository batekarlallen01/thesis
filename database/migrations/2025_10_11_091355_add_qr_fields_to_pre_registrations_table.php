<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    public function up()
    {
        Schema::table('pre_registrations', function (Blueprint $table) {
            $table->string('qr_token')->unique()->nullable()->after('address');
            $table->timestamp('qr_expires_at')->nullable()->after('qr_token');
            $table->string('pin_code', 6)->nullable()->after('qr_expires_at');
            $table->string('qr_image_path')->nullable()->after('pin_code');
            $table->boolean('has_entered_queue')->default(false)->after('qr_image_path');
        });
    }

    public function down()
    {
        Schema::table('pre_registrations', function (Blueprint $table) {
            $table->dropColumn(['qr_token', 'qr_expires_at', 'pin_code', 'qr_image_path', 'has_entered_queue']);
        });
    }
};