<?php

declare(strict_types=1);

namespace App\Command;

use App\Model\FossnirDir;
use App\Model\FossnirData;
use App\Event\NewFossnirData;
use Psr\Container\ContainerInterface;
use Hyperf\Command\Annotation\Command;
use Hyperf\Command\Command as HyperfCommand;
use Symfony\Component\Console\Input\InputArgument;

#[Command]
class DailyScoreFossnirCommand extends HyperfCommand
{
    public function __construct(protected ContainerInterface $container)
    {
        parent::__construct('fossnir:daily');
    }

    public function configure()
    {
        parent::configure();
        $this->setDescription('Create daily calculate');
    }

    protected function getArguments()
    {
        return [
            ['date', InputArgument::OPTIONAL, 'date'],
            ['mill_id', InputArgument::OPTIONAL, 'mill_id'],
        ];
    }

    public function handle()
    {
        $this->line('Hello Hyperf!', 'info');

        $date = $this->input->getArgument('date');
        $millId = $this->input->getArgument('mill_id', null);

        if($millId) {
            $rows = FossnirData::table($millId)->where('sample_date', '>=', $date)->get();
            foreach($rows as $row) {
                \dispatch(new NewFossnirData($row));
            }
        }else{
            $dirs = FossnirDir::orderBy('order')->get();
            foreach($dirs as $mill) {
                $rows = FossnirData::table($mill->id)->where('sample_date', '>=', $date)->get();
                foreach($rows as $row) {
                    \dispatch(new NewFossnirData($row));
                }
            }
        }
    }
}
