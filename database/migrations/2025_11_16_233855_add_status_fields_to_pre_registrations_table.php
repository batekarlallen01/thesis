<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pre_registrations', function (Blueprint $table) {
            // Add document columns if they don't exist
            if (!Schema::hasColumn('pre_registrations', 'owner_id_image')) {
                $table->string('owner_id_image')->nullable()->after('email');
            }
            if (!Schema::hasColumn('pre_registrations', 'spa_image')) {
                $table->string('spa_image')->nullable()->after('owner_id_image');
            }
            if (!Schema::hasColumn('pre_registrations', 'rep_id_image')) {
                $table->string('rep_id_image')->nullable()->after('spa_image');
            }
            if (!Schema::hasColumn('pre_registrations', 'tax_decl_form')) {
                $table->string('tax_decl_form')->nullable()->after('rep_id_image');
            }
            if (!Schema::hasColumn('pre_registrations', 'title')) {
                $table->string('title')->nullable()->after('tax_decl_form');
            }
            if (!Schema::hasColumn('pre_registrations', 'tax_payment')) {
                $table->string('tax_payment')->nullable()->after('title');
            }
            if (!Schema::hasColumn('pre_registrations', 'latest_tax_decl')) {
                $table->string('latest_tax_decl')->nullable()->after('tax_payment');
            }
            if (!Schema::hasColumn('pre_registrations', 'deed_of_sale')) {
                $table->string('deed_of_sale')->nullable()->after('latest_tax_decl');
            }
            if (!Schema::hasColumn('pre_registrations', 'transfer_tax_receipt')) {
                $table->string('transfer_tax_receipt')->nullable()->after('deed_of_sale');
            }
            if (!Schema::hasColumn('pre_registrations', 'car_from_bir')) {
                $table->string('car_from_bir')->nullable()->after('transfer_tax_receipt');
            }

            // Add status workflow columns
            if (!Schema::hasColumn('pre_registrations', 'status')) {
                $table->enum('status', ['pending', 'approved', 'disapproved'])
                      ->default('pending')
                      ->after('car_from_bir');
            }
            
            if (!Schema::hasColumn('pre_registrations', 'disapproval_reasons')) {
                $table->json('disapproval_reasons')->nullable()->after('status');
            }
            
            if (!Schema::hasColumn('pre_registrations', 'disapproval_other_reason')) {
                $table->text('disapproval_other_reason')->nullable()->after('disapproval_reasons');
            }
            
            if (!Schema::hasColumn('pre_registrations', 'reviewed_by')) {
                $table->unsignedBigInteger('reviewed_by')->nullable()->after('disapproval_other_reason');
            }
            
            if (!Schema::hasColumn('pre_registrations', 'reviewed_at')) {
                $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');
            }
            
            if (!Schema::hasColumn('pre_registrations', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('reviewed_at');
            }
            
            if (!Schema::hasColumn('pre_registrations', 'disapproved_at')) {
                $table->timestamp('disapproved_at')->nullable()->after('approved_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pre_registrations', function (Blueprint $table) {
            $columns = [
                'owner_id_image',
                'spa_image',
                'rep_id_image',
                'tax_decl_form',
                'title',
                'tax_payment',
                'latest_tax_decl',
                'deed_of_sale',
                'transfer_tax_receipt',
                'car_from_bir',
                'status',
                'disapproval_reasons',
                'disapproval_other_reason',
                'reviewed_by',
                'reviewed_at',
                'approved_at',
                'disapproved_at'
            ];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('pre_registrations', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};