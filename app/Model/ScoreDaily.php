<?php

declare(strict_types=1);

namespace App\Model;

use Hyperf\DbConnection\Model\Model;

/**
 */
class ScoreDaily extends Model
{
    /**
     * The table associated with the model.
     */
    protected ?string $table = 'score_daily';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = [
        'date', 'mill_id', 'device_id', 'group', 'score_total', 'score_count', 'score'
    ];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = [
        'date' => 'date:Y-m-d'
    ];

    /**
     * mill 
     */
    public function mill() {
        return $this->belongsTo(FossnirDir::class, 'mill_id');
    }

}
