<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class AddCsvDirRead extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('csv_read', function (Blueprint $table) {
            $table->unsignedBigInteger('mill_id')->nullable()->after('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('csv_read', function (Blueprint $table) {
            $table->dropColumn('mill_id');
        });
    }
}
