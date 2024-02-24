<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

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
        Schema::dropIfExists('fossnir_report_daily');
    }
}
