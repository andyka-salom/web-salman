<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE broadcast_messages
            MODIFY COLUMN status ENUM(
                'queued','pending','processing','scheduled',
                'completed','failed','cancelled'
            ) NOT NULL DEFAULT 'queued'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE broadcast_messages
            MODIFY COLUMN status ENUM(
                'pending','processing','scheduled',
                'completed','failed','cancelled'
            ) NOT NULL DEFAULT 'pending'");
    }
};
