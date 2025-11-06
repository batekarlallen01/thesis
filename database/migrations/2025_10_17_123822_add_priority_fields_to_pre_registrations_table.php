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
            $table->integer('age')->nullable()->after('full_name');
            $table->boolean('is_pwd')->default(false)->after('age');
            $table->string('pwd_id')->nullable()->after('is_pwd');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pre_registrations', function (Blueprint $table) {
            $table->dropColumn(['age', 'is_pwd', 'pwd_id']);
        });
    }
};