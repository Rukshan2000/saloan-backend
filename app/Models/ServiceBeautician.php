<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceBeautician extends Model
{
    protected $fillable = [
        'service_id', 'beautician_id'
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function beautician()
    {
        return $this->belongsTo(User::class, 'beautician_id');
    }
}
