<?php

declare(strict_types=1);

namespace App\Command;

use Carbon\Carbon;
use App\Handler\FossnirHandler;
use Psr\Container\ContainerInterface;
use Hyperf\Command\Annotation\Command;
use Hyperf\Command\Command as HyperfCommand;
use Symfony\Component\Console\Input\InputArgument;

#[Command]
class ReCountFossnirScore extends HyperfCommand
{
    public function __construct(protected ContainerInterface $container)
    {
        parent::__construct('fossnir:recount-score');
    }

    public function configure()
    {
        parent::configure();
        $this->setDescription('ReCount score fossnir');
    }

    public function handle()
    {
        $date = $this->input->getArgument('date');
        $date = $date ? Carbon::parse($date) : Carbon::now();

        $diff = Carbon::now()->diffInDays($date);
        
        if($diff > 0) {
            $handler = new FossnirHandler;
            for($i = 0; $i < $diff; $i++) {
                $nd = $date->copy()->addDay($i);
                // var_dump($nd->format('c'));
                $handler->count($nd);
            }
        }

    }

    protected function getArguments()
    {
        return [
            ['date', InputArgument::OPTIONAL, 'date'],
        ];
    }
}
