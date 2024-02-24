<?php

declare(strict_types=1);

namespace App\Model;

use Hyperf\DbConnection\Model\Model;

/**
 */
class FossnirReportDaily extends Model
{
    /**
     * The table associated with the model.
     */
    protected ?string $table = 'fossnir_report_daily';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = [
        'mill_id', 'group_id', 'sample_date', 'owm', 'vm', 'odm', 'nos'
    ];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = [
        'sample_date' => 'date'
    ];
}
