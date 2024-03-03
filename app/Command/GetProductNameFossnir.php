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

use App\Model\CSVRead;
use App\Model\FossnirDir;
use App\Model\FossnirProduct;
use Hyperf\Command\Annotation\Command;
use Hyperf\Command\Command as HyperfCommand;
use Psr\Container\ContainerInterface;

#[Command]
class GetProductNameFossnir extends HyperfCommand
{
    public function __construct(protected ContainerInterface $container)
    {
        parent::__construct('fossnir:product-parse');
    }

    public function configure()
    {
        parent::configure();
        $this->setDescription('Get product name from data csv readed');
    }

    public function handle()
    {
        foreach (FossnirDir::all() as $mill) {
            $products = CSVRead::table($mill->id)
                ->select('product_name', 'parameter')
                ->groupBy('product_name')
                ->groupBy('parameter')
                ->get();

            foreach ($products as $product) {
                FossnirProduct::updateOrCreate([
                    'product_name' => $product->product_name,
                    'parameter' => $product->parameter,
                    'mill_id' => $mill->id,
                ], []);
            }
        }
    }
}
