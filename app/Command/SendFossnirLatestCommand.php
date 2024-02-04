<?php

declare(strict_types=1);

namespace App\Command;

use Carbon\Carbon;
use App\Model\CSVRead;
use App\Model\FossnirDir;
use Hyperf\Stringable\Str;
use App\Model\FossnirProduct;
use PhpMqtt\Client\MqttClient;
use Psr\Container\ContainerInterface;
use Hyperf\Command\Annotation\Command;
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

    protected function getArguments()
    {
        return [
            ['mill_id', InputArgument::OPTIONAL, 'mill id'],
            ['date', InputArgument::OPTIONAL, 'date']
        ];
    }

    public function handle()
    {
        $mill_id = $this->input->getArgument('mill_id');
        $inDays = 90;
        $date = $this->input->getArgument('date') ?? Carbon::now()->format('Y-m-d');
        
        $from = Carbon::parse($date)->subDays($inDays);
        $to = Carbon::parse($date);

        if($mill_id) {
            $mill = FossnirDir::find($mill_id);
            $products = FossnirProduct::where('mill_id', $mill_id)->get();
            $data = [];
            foreach($products as $product) {
                $latest = CSVRead::table($mill->id)
                    ->whereDate('sample_date', '>=', $from)
                    ->whereDate('sample_date', '<=', $to)
                    ->where('product_name', $product->product_name)
                    ->where('parameter' , $product->parameter)
                    ->latest()
                    ->first();

                if($latest) {
                    $data[$product->product_name][$product->parameter] = [
                        'date' => $latest->sample_date->format('Y-m-d H:i:s'),
                        'result' => $latest->result
                    ];
                }
            }
            
            $this->send('data/bga/fossnir/'. strtolower($mill->mill_name), $data);
            if((bool) env('APP_DEBUG', false)) {
                $this->send2('data/bga/fossnir/'. strtolower($mill->mill_name), $data);
            }
        }
    }

    private function send(string $topic, array $data)  {
        
        $config = config('mqtt')['servers']['bga'];

        $clientId = Str::random(10);
        $mqtt = new MqttClient($config['host'], $config['port'], $clientId);
        $mqttSetting = (new \PhpMqtt\Client\ConnectionSettings)
            ->setUsername($config['username'])
            ->setPassword($config['password']);

        $mqtt->connect($mqttSetting, true);
        $mqtt->publish($topic, json_encode($data), 0);
    }

    private function send2(string $topic, array $data)  {
        
        $config = config('mqtt')['servers']['hivemq'];

        $clientId = Str::random(10);
        $mqtt = new MqttClient($config['host'], $config['port'], $clientId);
        $mqttSetting = (new \PhpMqtt\Client\ConnectionSettings)
            ->setUsername($config['username'])
            ->setPassword($config['password']);

        $mqtt->connect($mqttSetting, true);
        $mqtt->publish($topic, json_encode($data), 0);
    }
}
