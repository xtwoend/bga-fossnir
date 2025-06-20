<?php

declare(strict_types=1);

namespace App\Model;

use App\Model\FossnirDir;
use Hyperf\DbConnection\Db;
use Hyperf\DbConnection\Model\Model;

/**
 */
class Sample extends Model
{
    /**
     * The table associated with the model.
     */
    protected ?string $table = 'samples';

    protected array $guarded = ['id'];
    
    protected array $casts = [
        'sample_date' => 'datetime',
    ];

    public static function byDate(string $date)
    {
        $date = strtotime($date);
        $table = 'samples_' . date('Ym', $date);

        $model = new self;
        
        return $model->table($table);
    }

    public function table(string $table)
    {
        $query = 'SHOW TABLES LIKE \'' . $table . '\';';

        $result = Db::select($query);
        if (empty($result)) {
            $query = 'CREATE TABLE `' . $table . '` LIKE `samples`;';
            Db::statement($query);
        }
        
        return self::setTable($table);
    }

    public function device() 
    {
        return $this->belongsTo(FossnirDir::class, 'device_id');
    }
}
