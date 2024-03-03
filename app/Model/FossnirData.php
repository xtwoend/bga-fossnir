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

namespace App\Model;

use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Schema\Schema;
use Hyperf\DbConnection\Model\Model;

class FossnirData extends Model
{
    /**
     * The table associated with the model.
     */
    protected ?string $table = 'fossnir_data';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = [
        'mill_id', 'sample_date', 'instrument_serial', 'product_name', 'owm', 'vm', 'odm', 'nos',
    ];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = [
        'sample_date' => 'datetime',
    ];

    public static function table($millId)
    {
        $model = new self();
        $tableName = $model->getTable() . "_{$millId}";

        if (! Schema::hasTable($tableName)) {
            Schema::create($tableName, function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('mill_id');
                $table->datetime('sample_date');
                $table->string('instrument_serial');
                $table->string('product_name');
                $table->float('owm', 5, 2)->nullable()->default(null);
                $table->float('vm', 5, 2)->nullable()->default(null);
                $table->float('odm', 5, 2)->nullable()->default(null);
                $table->float('nos', 5, 2)->nullable()->default(null);
                $table->datetimes();
            });
        }

        return $model->setTable($tableName);
    }

    /**
     * mill dir.
     */
    public function mill()
    {
        return $this->belongsTo(FossnirDir::class, 'mill_id');
    }
}
