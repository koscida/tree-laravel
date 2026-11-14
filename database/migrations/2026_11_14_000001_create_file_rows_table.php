<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('file_rows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('file_upload_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_header');
            $table->integer('row_number');
            $table->json('data');
            $table->timestamps();

            $table->unique(['file_upload_id', 'row_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('file_rows');
    }
};
