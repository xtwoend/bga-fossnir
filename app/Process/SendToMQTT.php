<?php

declare(strict_types=1);

namespace App\Process;

use App\Model\FossnirData;
use App\Model\FossnirDir;
use Hyperf\Process\AbstractProcess;
use Hyperf\Process\Annotation\Process;
use Hyperf\Stringable\Str;
use PhpMqtt\Client\ConnectionSettings;
use PhpMqtt\Client\MqttClient;

use function Hyperf\Config\config;

#[Process(name: 'SendToMQTT')]
class SendToMQTT extends AbstractProcess
{
    public function handle(): void
    {
        while (true) {
            $this->sendToMQTT();
            // Sleep for a short duration to avoid busy waiting
            sleep(5); // sleep for 5 seconds before checking the queue again
        }
    }

    protected function sendToMQTT()
    {
        $mills = [12, 15]; // SCMM, TRYM
        foreach ($mills as $mill_id) {
            $date = date('Y-m-d');
            $mill = FossnirDir::find($mill_id);
            $data1 =  [];

            $products = FossnirData::table($mill_id)
                ->select('product_name')
                ->groupBy('product_name')
                ->pluck('product_name')
                ->toArray();


            foreach($products as $product){
                $latest = FossnirData::table($mill_id)->where('product_name', $product)->orderBy('sample_date', 'desc')->limit(2)->get();
                if($latest) {
                    $data1[$product] = $latest->toArray();
                }
            }
            
            $data2 = [];
            foreach ($products as $product) {
                $latest = FossnirData::table($mill_id)->where('product_name', $product)->whereDate('sample_date', '<=', $date)->orderBy('sample_date', 'desc')->first();
                if ($latest) {
                    $productName = str_replace(' ', '_', strtolower($product));
                    $data2[$productName] = (string) $latest->owm ?? null; 
                }
            }
            
            $this->send('data/bga/fossnir/' . strtolower($mill->mill_name), $data1);
            $this->send('data/bga/fossnir/latest/' . strtolower($mill->mill_name), $data2);
        }
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
}
