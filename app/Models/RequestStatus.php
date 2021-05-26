<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestStatus extends Model
{
    use HasFactory;
    public $timestamps = false;


    public $table = "status";
    const PENDING = 1;
    const APPROVED = 2;
    const REJECTED = 3;
}
