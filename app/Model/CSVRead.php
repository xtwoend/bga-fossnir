<?php

declare(strict_types=1);

namespace App\Model;

use Hyperf\Database\Schema\Schema;
use Hyperf\DbConnection\Model\Model;
use Hyperf\Database\Schema\Blueprint;

/**
 */
class CSVRead extends Model
{
    /**
     * The table associated with the model.
     */
    protected ?string $table = 'csv_read';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = [
        'mill_id', 'instrument_serial', 'product_name', 'parameter', 'result', 'sample_date'
    ];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = [
        'sample_date' => 'datetime'
    ];

    public static function table($millId)
    {
        $model = new self;
        $tableName = $model->getTable() . "_{$millId}";
        
        if(! Schema::hasTable($tableName)) {
            Schema::create($tableName, function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('mill_id')->nullable();
                $table->datetime('sample_date')->index();
                $table->string('instrument_serial');
                $table->string('product_name')->index();
                $table->string('parameter')->index();
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

        return $model->setTable($tableName);
    }

    /**
     * mill dir
     */
    public function mill() 
    {
        return $this->belongsTo(FossnirDir::class, 'mill_id');
    }
}
