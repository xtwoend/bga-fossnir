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

class CreateFossnirReportDailyTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('fossnir_report_daily', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('mill_id');
            $table->unsignedBigInteger('group_id');
            $table->date('sample_date');
            $table->float('owm', 5, 2)->default(null);
            $table->float('vm', 5, 2)->default(null);
            $table->float('odm', 5, 2)->default(null);
            $table->float('nos', 5, 2)->default(null);
            $table->datetimes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fossnir_report_daily');
    }
}
