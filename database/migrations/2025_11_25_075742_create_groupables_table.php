<?php

// database/migrations/xxxx_xx_xx_create_groupables_table.php

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
        Schema::create('groupables', function (Blueprint $table) {
            $table->foreignUuid('group_id')
                ->constrained('groups')
                ->cascadeOnDelete();
            $table->uuid('groupable_id')->index();
            $table->string('groupable_type', 100)->index();
            $table->timestamps();

            // Composite primary key
            $table->primary(
                ['group_id', 'groupable_id', 'groupable_type'],
                'groupables_pk'
            );

            // Additional index for polymorphic relationship
            $table->index(['groupable_type', 'groupable_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('groupables');
    }
};
