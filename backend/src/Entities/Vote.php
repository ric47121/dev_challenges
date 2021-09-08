<?php
namespace Entities;

class Vote{

    public $status; //wait, voted, pased
    public $value;

    public function __construct(string $status = 'wait', int $value = -1)
    {
        $this->status = $status;
        $this->value = $value;
    }

}