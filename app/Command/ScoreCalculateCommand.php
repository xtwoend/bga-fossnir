<?php

declare(strict_types=1);

namespace App\Command;

use Hyperf\Command\Annotation\Command;
use Hyperf\Command\Command as HyperfCommand;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\InputArgument;

#[Command]
class ScoreCalculateCommand extends HyperfCommand
{
    public function __construct(protected ContainerInterface $container)
    {
        parent::__construct('score:calculate');
    }

    public function configure()
    {
        parent::configure();
        $this->setDescription('Calculate scores');
    }

    public function handle()
    {
        $millId = $this->input->getArgument('mill_id') ?? null;
        $date = $this->input->getArgument('date') ?? date('Y-m-d');

        if($millId) {
            $this->line("Calculating scores for mill ID: $millId on date: $date", 'info');  
        }else {
            $this->line("Calculating scores for all mills on date: $date", 'info');
        }

        $this->line("Calculating scores for mill ID: $millId on date: $date", 'info');
    }

    protected function getArguments()
    {
        return [
            ['mill_id', InputArgument::OPTIONAL, 'Score calculate for mill id'],
            ['date', InputArgument::OPTIONAL, 'Score calculate for date'],
        ];
    }
}
