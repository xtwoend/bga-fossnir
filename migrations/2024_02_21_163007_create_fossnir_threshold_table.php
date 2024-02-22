<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateFossnirThresholdTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('fossnir_threshold', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('mill_id');
            $table->unsignedBigInteger('group_id');
            $table->string('parameter', 20)->nullable();
            $table->float('threshold')->default(0);
            $table->datetimes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fossnir_threshold');
    }
}
