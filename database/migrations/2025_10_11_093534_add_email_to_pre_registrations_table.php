<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    public function up()
    {
        Schema::table('pre_registrations', function (Blueprint $table) {
            $table->string('email')->nullable()->after('address');
        });
    }

    public function down()
    {
        Schema::table('pre_registrations', function (Blueprint $table) {
            $table->dropColumn('email');
        });
    }
};