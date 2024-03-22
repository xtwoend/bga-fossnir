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

namespace App\Command;

use Carbon\Carbon;
use App\Handler\FossnirHandler;
use Psr\Container\ContainerInterface;
use Hyperf\Command\Annotation\Command;
use Hyperf\Command\Command as HyperfCommand;
use Symfony\Component\Console\Input\InputArgument;

#[Command]
class FossnirDailyReportCommand extends HyperfCommand
{
    public function __construct(protected ContainerInterface $container)
    {
        parent::__construct('fossnir:daily-count');
    }

    public function configure()
    {
        parent::configure();
        $this->setDescription('Save daily result fossnir all mill');
    }

    public function handle()
    {
        $date = $this->input->getArgument('date');
        $date = $date ? Carbon::parse($date) : Carbon::now();

        $handler = new FossnirHandler;
        $handler->count($date);
    }

    protected function getArguments()
    {
        return [
            ['date', InputArgument::OPTIONAL, 'date'],
        ];
    }
}
