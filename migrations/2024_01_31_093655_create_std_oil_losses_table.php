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

class CreateStdOilLossesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('std_oil_losses', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('mill_id');
            $table->string('product_name')->nullable();
            $table->string('group')->nullable();
            $table->string('parameter')->nullable();
            $table->float('value', 5, 2)->default(0);
            $table->datetimes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('std_oil_losses');
    }
}
