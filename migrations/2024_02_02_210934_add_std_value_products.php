<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class AddStdValueProducts extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('fossnir_products', function (Blueprint $table) {
            $table->float('std_value', 6,2)->default(0)->after('parameter');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fossnir_products', function (Blueprint $table) {
            $table->dropColumn('std_value');
        });
    }
}
