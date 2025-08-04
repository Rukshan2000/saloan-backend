<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Appointment;

class Branch extends Model
{
    protected $fillable = [
        'name', 'address', 'contact', 'phone', 'email'
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    // Accessor for phone (fallback to contact if no dedicated phone field)
    public function getPhoneAttribute($value)
    {
        return $value ?? $this->contact;
    }
}
