<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BeauticianAvailability extends Model
{
    protected $table = 'beautician_availability';
    protected $fillable = [
        'beautician_id', 'day_of_week', 'start_time', 'end_time'
    ];

    public function beautician()
    {
        return $this->belongsTo(User::class, 'beautician_id');
    }
}
