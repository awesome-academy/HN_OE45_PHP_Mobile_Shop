<?php

namespace App\Http\Resources;

class OrderCollection extends ApiCollection
{
    public function getApiAllOrder()
    {
        return $this->collection;
    }
}
