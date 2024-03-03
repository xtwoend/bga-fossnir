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

class AddOrderingMill extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('fossnir_dir', function (Blueprint $table) {
            $table->tinyInteger('order')->nullable()->after('dir_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fossnir_dir', function (Blueprint $table) {
            $table->dropColumn('order');
        });
    }
}
