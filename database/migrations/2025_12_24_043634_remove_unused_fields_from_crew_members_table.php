<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('crew_members', function (Blueprint $table) {
        $table->dropColumn(['gender', 'birth_date', 'age', 'blood_type', 'address']);
    });
}

public function down()
{
    Schema::table('crew_members', function (Blueprint $table) {
        $table->enum('gender', ['male', 'female', 'other'])->nullable();
        $table->date('birth_date')->nullable();
        $table->unsignedTinyInteger('age')->nullable();
        $table->string('blood_type', 5)->nullable();
        $table->text('address')->nullable();
    });
}
};
