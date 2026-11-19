<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('file_mappings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('file_upload_id')->constrained()->cascadeOnDelete();
            $table->integer('column_idx');
            $table->enum('map_kind', [
                'relationship',
                'milestone_date',
                'milestone_location',
                'record_label',
                'skip',
            ]);
            $table->foreignId('relationship_id')->nullable()->constrained();
            $table->foreignId('name_part_id')->nullable()->constrained();
            $table->foreignId('milestone_id')->nullable()->constrained();
            $table->foreignId('date_part_id')->nullable()->constrained();
            $table->foreignId('record_label_id')->nullable()->constrained();
            $table->timestamps();

            $table->unique(['file_upload_id', 'column_idx']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('file_mappings');
    }
};
