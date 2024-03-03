<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Schema\Schema;

class CreateCsvReadTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('csv_read', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->datetime('sample_date')->nullable();
            $table->string('instrument_serial');
            $table->string('product_name');
            $table->string('parameter')->nullable();
            $table->float('result', 5, 2)->nullable();
            $table->float('udf_1', 6, 2)->nullable();
            $table->float('udf_2', 6, 2)->nullable();
            $table->float('udf_3', 6, 2)->nullable();
            $table->float('udf_4', 6, 2)->nullable();
            $table->float('udf_5', 6, 2)->nullable();
            $table->float('udf_6', 6, 2)->nullable();
            $table->boolean('cancelled_flag')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('csv_read');
    }
}
