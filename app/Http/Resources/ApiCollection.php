<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Response;

abstract class ApiCollection extends ResourceCollection
{
    public $resource;
    public $message;
    public $method;

    public function __construct($resource, $method = 'index', $message = null)
    {
        parent::__construct($resource);
        $this->method = $method;
        $this->message = $message;
    }

    public function with($request)
    {
        return [
            'messages' => $this->message ? [$this->message] : [],
            'code' => Response::HTTP_OK,
        ];
    }

     /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
       return $this->{$this->method}();
    }
}
