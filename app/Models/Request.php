<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Request extends Model
{
    use HasFactory;

    protected $table = "requests";
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = true;
    const CREATED_AT = 'request_created_at';
    const UPDATED_AT = null;

    protected $fillable = ['author','status', 'resolved_by', 'vacation_start_date', 'vacation_end_date', 'request_created_at'];

    public function status()
    {
        return $this->belongsTo('App\Models\RequestStatus', 'status');
    }

    public function author()
    {
        return $this->belongsTo('App\Models\User', 'author');
    }

    public function resolveBy()
    {
        return $this->belongsTo('App\Models\User', 'resolved_by');
    }
}
