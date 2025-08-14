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
use App\Model\CSVRead;
use App\Model\FossnirDir;
use App\Model\FossnirData;
use Hyperf\Stringable\Str;
use App\Model\GroupProduct;
use App\Model\FossnirProduct;
use PhpMqtt\Client\MqttClient;
use function Hyperf\Support\env;
use function Hyperf\Config\config;
use Psr\Container\ContainerInterface;
use Hyperf\Command\Annotation\Command;
use PhpMqtt\Client\ConnectionSettings;
use Hyperf\Command\Command as HyperfCommand;
use Symfony\Component\Console\Input\InputArgument;

#[Command]
class SendFossnirLatestCommand extends HyperfCommand
{
    public function __construct(protected ContainerInterface $container)
    {
        parent::__construct('fossnir:send');
    }

    public function configure()
    {
        parent::configure();
        $this->setDescription('Send Fossnir latest record');
    }

    public function handle()
    {
        $mill_id = $this->input->getArgument('mill_id');
        $date = $this->input->getArgument('date') ?? Carbon::now()->format('Y-m-d');

        if ($mill_id) {
            $mill = FossnirDir::find($mill_id);
            // $products = FossnirData::table($mill_id)
            //     ->select('product_name')
            //     ->groupBy('product_name')
            //     ->pluck('product_name')
            //     ->toArray();

            $data =  [];

            $names = GroupProduct::where('mill_id', $mill_id)->get()->pluck('product_name')->toArray();
            foreach($names as $product){
                $latest = FossnirData::table($mill_id)->where('product_name', $product)->orderBy('sample_date', 'desc')->limit(2)->get();
                if($latest) {
                    $data[$product] = $latest->toArray();
                }
            }
            
            var_dump($data);
            $this->send('data/bga/fossnir/' . strtolower($mill->mill_name), $data);
            if ((bool) env('APP_DEBUG', false)) {
                $this->send2('data/bga/fossnir/' . strtolower($mill->mill_name), $data);
            }
        }
    }

    protected function getArguments()
    {
        return [
            ['mill_id', InputArgument::OPTIONAL, 'mill id'],
            ['date', InputArgument::OPTIONAL, 'date'],
        ];
    }

    private function send(string $topic, array $data)
    {
        $config = config('mqtt')['servers']['bga'];

        $clientId = Str::random(10);
        $mqtt = new MqttClient($config['host'], $config['port'], $clientId);
        $mqttSetting = (new ConnectionSettings())
            ->setUsername($config['username'])
            ->setPassword($config['password']);

        $mqtt->connect($mqttSetting, true);
        $mqtt->publish($topic, json_encode($data), 0);
    }

    private function send2(string $topic, array $data)
    {
        $config = config('mqtt')['servers']['hivemq'];

        $clientId = Str::random(10);
        $mqtt = new MqttClient($config['host'], $config['port'], $clientId);
        $mqttSetting = (new ConnectionSettings())
            ->setUsername($config['username'])
            ->setPassword($config['password']);

        $mqtt->connect($mqttSetting, true);
        $mqtt->publish($topic, json_encode($data), 0);
    }
}
