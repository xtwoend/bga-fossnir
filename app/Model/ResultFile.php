<?php

declare(strict_types=1);

namespace App\Model;

use Hyperf\DbConnection\Model\Model;

/**
 */
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
        'mill_id', 'filename', 'path', 'download_path', 'processed', 'filesize', 'modified_at'
    ];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = [
        'processed' => 'boolean'
    ];

    /**
     * mill dir
     */
    public function mill() 
    {
        return $this->belongsTo(FossnirDir::class, 'mill_id');
    }
}
