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

class ResultFile extends Model
{
    /**
     * The table associated with the model.
     */
    protected ?string $table = 'result_files';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = [
        'mill_id', 'filename', 'path', 'download_path', 'processed', 'filesize', 'modified_at',
    ];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = [
        'processed' => 'boolean',
    ];

    public static function table($millId)
    {
        $model = new self();
        $tableName = $model->getTable() . "_{$millId}";

        if (! Schema::hasTable($tableName)) {
            Schema::create($tableName, function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('mill_id')->nullable();
                $table->string('filename')->nullable();
                $table->string('path')->index();
                $table->string('download_path')->index();
                $table->boolean('processed')->default(false);
                $table->integer('filesize')->nullable();
                $table->integer('modified_at')->nullable();
                $table->timestamps();
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
