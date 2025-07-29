<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Branch;
use App\Models\AppointmentService;
use App\Models\Invoice;

class Appointment extends Model
{
    protected $fillable = [
        'customer_id', 'beautician_id', 'branch_id', 'date', 'status', 'receipt_number'
    ];

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function beautician()
    {
        return $this->belongsTo(User::class, 'beautician_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function services()
    {
        return $this->hasMany(AppointmentService::class);
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }
}
