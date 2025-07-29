<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    protected $fillable = [
        'type', 'code', 'value', 'max_discount', 'min_amount', 'start_date', 'end_date'
    ];
}
