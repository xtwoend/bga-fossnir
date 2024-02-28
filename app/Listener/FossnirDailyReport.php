<?php

declare(strict_types=1);

namespace App\Listener;

use App\Model\FossnirData;
use Hyperf\Event\Annotation\Listener;
use Psr\Container\ContainerInterface;
use Hyperf\Database\Model\Events\Created;
use Hyperf\Event\Contract\ListenerInterface;

#[Listener]
class FossnirDailyReport implements ListenerInterface
{
    public function __construct(protected ContainerInterface $container)
    {
    }

    public function listen(): array
    {
        return [
            Created::class
        ];
    }

    public function process(object $event): void
    {
        $model = $event->getModel();
        if($model instanceof FossnirData)
        {
            // 
        }
    }
}
