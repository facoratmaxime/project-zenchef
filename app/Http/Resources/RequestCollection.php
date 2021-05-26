<?php

namespace App\Http\Resources;

use App\Models\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class RequestCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'data' => $this->collection,
            'links' => [
                'self' => 'link-value',
            ],
        ];
    }

    /**
     * The resource that this resource collects.
     *
     * @var string
     */
    public $collects = Request::class;
}
