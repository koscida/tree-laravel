<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('file_rows_log', function (Blueprint $table) {
            $table->id();
            $table->string('uuid');
            $table->foreignId('file_upload_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['started', 'error', 'completed']);
            $table->text('message')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('file_rows_log');
    }
};
