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

use App\Model\News;
use App\Model\FossnirDir;
use App\Model\GroupProduct;
use App\Model\FossnirThreshold;
use Hyperf\Database\Schema\Schema;
use Hyperf\DbConnection\Model\Model;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Model\Events\Created;
use function Hyperf\Support\make;

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
                $table->datetime('sample_date')->index();
                $table->string('instrument_serial');
                $table->string('product_name')->index();
                $table->float('owm', 5, 2)->nullable()->default(null);
                $table->float('vm', 5, 2)->nullable()->default(null);
                $table->float('odm', 5, 2)->nullable()->default(null);
                $table->float('nos', 5, 2)->nullable()->default(null);
                $table->datetimes();
                
                $table->index(['sample_date', 'product_name']);
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

    /**
     * score custom
     */
    public function scopeWhereDateBetween($query, $fieldName, array $date)
    {   
        if(count($date) !== 2) return $query;

        $fromDate = $date[0];
        $toDate = $date[1];

        return $query
            ->whereDate($fieldName, '>=', $fromDate)
            ->whereDate($fieldName, '<=', $toDate);
    }

    /**
     * created event
     */
    public function created(Created $event)
    {
        $model = $event->getModel();

        $inGroup = GroupProduct::where('mill_id', $model->mill_id)->where('product_name', $model->product_name)->first();
        $mill = FossnirDir::find($model->mill_id);
    
        if($inGroup) {
            
            $treshold = FossnirThreshold::where('mill_id', $model->mill_id)->where('group_id', $inGroup->group_id)->where('parameter', 'owm')->first();
            if($treshold && $model->owm > $treshold->threshold) {
                $users = \App\Model\TelegramUser::where('mill_id', $model->mill_id)->get();
                $t = make(\App\Service\Telegram::class);
                $text = sprintf("%s aktual %.2f %%  diatas standart %.2f %% O/WM", $model->product_name, $model->owm, $treshold->threshold);
                $text = "[{$mill->mill_name}][{$model->sample_date}] {$text}";
               
                News::create([
                    'mill_id' => $mill->id,
                    'content' => $text,
                ]);

                foreach($users as $user) {
                    $t->send($user->chat_id, $text);
                }
            }
        }
    }
}
