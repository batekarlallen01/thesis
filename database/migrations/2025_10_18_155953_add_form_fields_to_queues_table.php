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
        $table->integer('number_of_copies')->nullable();
        $table->text('purpose')->nullable();
        $table->text('address')->nullable();
        $table->string('applicant_type')->nullable();
        $table->string('govt_id_type')->nullable();
        $table->string('govt_id_number')->nullable();
        $table->string('issued_at')->nullable();
        $table->date('issued_on')->nullable();
        $table->string('pin_land')->nullable();
        $table->string('pin_building')->nullable();
        $table->string('pin_machinery')->nullable();
    });
}

public function down(): void
{
    Schema::table('queues', function (Blueprint $table) {
        $table->dropColumn(['number_of_copies', 'purpose', 'address', 'applicant_type', 'govt_id_type', 'govt_id_number', 'issued_at', 'issued_on', 'pin_land', 'pin_building', 'pin_machinery']);
    });
}
};
