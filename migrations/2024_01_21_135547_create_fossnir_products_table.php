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

class CreateFossnirProductsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('fossnir_products', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('mill_id');
            $table->string('product_name', 80);
            $table->string('parameter', 10);
            $table->datetimes();

            $table->unique(['mill_id', 'product_name', 'parameter'], 'mill_production_stations');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fossnir_products');
    }
}
