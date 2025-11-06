<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('service_requests', function (Blueprint $table) {
            $table->id();
            $table->string('service_type');
            $table->string('applicant_type');
            $table->integer('number_of_copies');
            $table->string('full_name');
            $table->text('purpose');
            $table->string('govt_id_type');
            $table->string('govt_id_number');
            $table->string('issued_at')->nullable();
            $table->date('issued_on')->nullable();
            $table->text('address');
            $table->json('pin_numbers')->nullable(); // Stores land/building/machinery PINs
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('service_requests');
    }
};