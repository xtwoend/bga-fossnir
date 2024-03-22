<?php

declare(strict_types=1);

namespace App\Listener;

use Carbon\Carbon;
use App\Model\FossnirData;
use App\Model\FossnirScore;
use App\Model\GroupProduct;
use Hyperf\DbConnection\Db;
use App\Event\NewFossnirData;
use App\Model\FossnirThreshold;
use Hyperf\Event\Annotation\Listener;
use Psr\Container\ContainerInterface;
use Hyperf\Event\Contract\ListenerInterface;

#[Listener]
class FossnirDataDailyListener implements ListenerInterface
{
    public function __construct(protected ContainerInterface $container)
    {
    }

    public function listen(): array
    {
        return [
            NewFossnirData::class,
        ];
    }

    public function process(object $event): void
    {
        // new data
    }
}
