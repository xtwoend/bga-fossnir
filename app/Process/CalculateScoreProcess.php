<?php

declare(strict_types=1);

namespace App\Process;

use Carbon\Carbon;
use App\Handler\FossnirHandler;
use Hyperf\Process\AbstractProcess;
use Hyperf\Process\Annotation\Process;

#[Process(name: 'CalculateScoreProcess')]
class CalculateScoreProcess extends AbstractProcess
{
    public function handle(): void
    {
        while(true) {
            $date = Carbon::now();
            $millId = 'all';

            $handler = new FossnirHandler;
            $handler->count($date, $millId);

            sleep(600);
        }
    }
}
