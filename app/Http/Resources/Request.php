<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Request extends JsonResource
{

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'author' => $this->worker_id,
            'status' => $this->status,
            'resolved_by' => $this->manager_id,
            'request_created_at' => $this->created_at,
            'vacation_start_date' => $this->vacation_start_date,
            'vacation_end_date' => $this->vacation_end_date
        ];
    }
}
