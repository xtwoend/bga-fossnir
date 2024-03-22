<?php
declare(strict_types=1);

namespace App\Process;

use App\Service\Telegram;
use Hyperf\Process\AbstractProcess;
use Hyperf\Process\Annotation\Process;

#[Process(name: "telegram_process")]
class TelegramProcess extends AbstractProcess
{
    public function handle(): void
    {
        $t = make(Telegram::class);
        $t->listen();
    }

    public function isEnable($server): bool
    {
        return env('TELEGRAM_ENABLE', true);
    }
}
