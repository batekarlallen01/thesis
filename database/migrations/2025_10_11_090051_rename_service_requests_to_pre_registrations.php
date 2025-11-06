<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    public function up()
    {
        Schema::rename('service_requests', 'pre_registrations');
    }

    public function down()
    {
        Schema::rename('pre_registrations', 'service_requests');
    }
};