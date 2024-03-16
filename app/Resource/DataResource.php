<?php

namespace App\Resource;

use Hyperf\Resource\Json\JsonResource;

class DataResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return parent::toArray();
    }
}
