<?php

namespace App\Http\Resources;

class OrderResource extends ApiResource
{
    public function show()
    {
        return $this->resource;
    }

    public function getApiDetailsOrder()
    {
        return $this->resource;
    }
}
