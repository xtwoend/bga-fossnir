<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class AddFlagRead extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('csv_read', function (Blueprint $table) {
            $table->float('udf1')->nullable()->after('result');
            $table->float('udf2')->nullable()->after('udf1');
            $table->float('udf3')->nullable()->after('udf2');
            $table->float('udf4')->nullable()->after('udf3');
            $table->float('udf5')->nullable()->after('udf4');
            $table->float('udf6')->nullable()->after('udf5');
            $table->boolean('cancelled_flag')->default(false)->after('udf6');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('csv_read', function (Blueprint $table) {
            $table->dropColumn(['udf1', 'udf2', 'udf3', 'udf4', 'udf5', 'udf6', 'cancelled_flag']);
        });
    }
}
