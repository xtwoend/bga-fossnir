<?php

declare(strict_types=1);

namespace App\Model;

use Hyperf\Database\Schema\Schema;
use Hyperf\DbConnection\Model\Model;
use Hyperf\Database\Schema\Blueprint;

/**
 */
class FossnirScore extends Model
{
    /**
     * The table associated with the model.
     */
    protected ?string $table = 'fossnir_scores';

    /**
     * The attributes that are mass assignable.
     */
    protected array $guarded = ['id'];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = [
        'sample_date' => 'date:Y-m-d'
    ];

    /**
     * create table
     */
    public static function table($millId)
    {
        $model = new self();
        $tableName = $model->getTable() . "_{$millId}";

        if (! Schema::hasTable($tableName)) {
            Schema::create($tableName, function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('mill_id');
                $table->date('sample_date')->index();
                $table->string('product_name')->index();
                $table->integer('sample_count')->default(0);
                $table->float('threshold_owm', 5, 2)->nullable();
                $table->float('threshold_vm', 5, 2)->nullable();
                $table->float('threshold_odm', 5, 2)->nullable();
                $table->float('threshold_nos', 5, 2)->nullable();
                $table->integer('score_owm')->default(0);
                $table->integer('score_vm')->default(0);
                $table->integer('score_odm')->default(0);
                $table->integer('score_nos')->default(0);
                $table->float('owm', 12, 2)->default(0);
                $table->float('vm', 12, 2)->default(0);
                $table->float('odm', 12, 2)->default(0);
                $table->float('nos', 12, 2)->default(0);
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
