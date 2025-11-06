<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    public function up()
    {
        Schema::table('pre_registrations', function (Blueprint $table) {
            // If using MySQL 5.7+, change to JSON
            if (Schema::hasColumn('pre_registrations', 'pin_numbers')) {
                $table->json('pin_numbers')->nullable()->change();
            }
        });
    }

    public function down()
    {
        Schema::table('pre_registrations', function (Blueprint $table) {
            $table->text('pin_numbers')->nullable()->change();
        });
    }
};