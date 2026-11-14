<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('file_uploads', function (Blueprint $table) {
            $table->tinyInteger('header_rows')->default(0)->after('record_type');
            $table->boolean('is_parsed')->default(false)->after('header_rows');
            $table->datetime('parsed_date')->nullable()->after('is_parsed');
        });
    }

    public function down(): void
    {
        Schema::table('file_uploads', function (Blueprint $table) {
            $table->dropColumn(['header_rows', 'is_parsed', 'parsed_date']);
        });
    }
};
