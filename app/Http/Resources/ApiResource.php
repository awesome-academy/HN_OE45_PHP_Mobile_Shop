<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;

abstract class ApiResource extends JsonResource
{
    public $message;
    public $method;

    public function __construct($resource, $method = 'show', $message = null)
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
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $method = is_string($this->method) ? $this->method : 'show';

        return $this->{$method}();
    }
}
