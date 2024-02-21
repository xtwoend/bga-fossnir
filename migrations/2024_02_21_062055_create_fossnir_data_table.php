<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateFossnirDataTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('fossnir_data', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('mill_id');
            $table->datetime('sample_date');
            $table->string('instrument_serial');
            $table->string('product_name');
            $table->float('owm', 5, 2)->default(NULL);
            $table->float('vm', 5, 2)->default(NULL);
            $table->float('odm', 5, 2)->default(NULL);
            $table->float('nos', 5, 2)->default(NULL);
            $table->datetimes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fossnir_data');
    }
}
