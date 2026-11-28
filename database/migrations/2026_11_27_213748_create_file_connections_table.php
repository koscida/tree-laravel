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
        Schema::create('file_connections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('file_row_id')->constrained()->cascadeOnDelete();
            $table->foreignId('file_mapping_id')->constrained()->cascadeOnDelete();
            $table->enum('match_method', ['auto', 'manual']);
            $table->enum('connection_kind', ['surname']);
            $table->foreignId('surname_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(
                ['file_row_id', 'file_mapping_id', 'connection_kind', 'surname_id'],
                'file_connections_cell_surname_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('file_connections');
    }
};
