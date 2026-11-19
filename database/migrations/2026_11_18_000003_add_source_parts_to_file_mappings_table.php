<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('source_parts', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('label');
            $table->timestamps();
        });

        Schema::table('file_mappings', function (Blueprint $table) {
            $table->enum('map_kind', [
                'relationship',
                'milestone_date',
                'milestone_location',
                'record_label',
                'skip',
                'source',
            ])->change();

            $table->foreignId('source_part_id')->nullable()->after('record_label_id')->constrained();
        });
    }

    public function down(): void
    {
        Schema::table('file_mappings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('source_part_id');
        });

        Schema::dropIfExists('source_parts');
    }
};
