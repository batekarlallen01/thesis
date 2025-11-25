<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check current enum values
        $current = DB::select("SHOW COLUMNS FROM queues LIKE 'status'");
        $enumStr = $current[0]->Type; // e.g., "enum('waiting','serving','completed','cancelled')"
        
        // Extract existing values
        preg_match_all("/'(.*?)'/", $enumStr, $matches);
        $values = $matches[1];

        // Only add 'requeued' if not already present
        if (!in_array('requeued', $values)) {
            $values[] = 'requeued';
            sort($values); // Optional: keep alphabetical order

            $newEnum = "'" . implode("','", $values) . "'";
            DB::statement("ALTER TABLE queues MODIFY COLUMN status ENUM({$newEnum}) NOT NULL DEFAULT 'waiting'");
            
            \Log::info('Migration: Added "requeued" to queue.status ENUM');
        } else {
            \Log::info('Migration: "requeued" already exists in queue.status');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE queues MODIFY COLUMN status ENUM('waiting','serving','completed','cancelled') NOT NULL DEFAULT 'waiting'");
    }
};