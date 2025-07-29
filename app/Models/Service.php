<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable = [
        'name', 'description', 'duration', 'price', 'category_id', 'active'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function beauticians()
    {
        return $this->belongsToMany(User::class, 'service_beauticians', 'service_id', 'beautician_id');
    }
}
