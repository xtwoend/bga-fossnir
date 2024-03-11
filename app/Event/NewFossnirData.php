<?php

namespace App\Event;

class NewFossnirData
{
    public $data;

    public function __construct($data) {
        $this->data = $data;
    }
}