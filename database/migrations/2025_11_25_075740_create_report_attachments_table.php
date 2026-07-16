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
        Schema::create('report_attachments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('cermat_report_id')
                ->constrained('cermat_reports')
                ->cascadeOnDelete();
            $table->foreignUuid('uploaded_by_id')->constrained('users');

            $table->enum('upload_stage', [
                'initial_report',
                'review',
                'action_item',
                'closeout'
            ])->index();

            $table->string('file_path', 500);
            $table->string('file_name', 255);
            $table->string('mime_type', 100)->nullable();
            $table->unsignedInteger('file_size')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['cermat_report_id', 'upload_stage']);
            $table->index('uploaded_by_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_attachments');
    }
};
