<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'appointment_id', 
        'customer_id', 
        'invoice_number', 
        'status', 
        'total'
    ];

    protected $casts = [
        'total' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationship to appointment
    public function appointment()
    {
        return $this->belongsTo(Appointment::class)->with([
            'services.service.category',
            'branch',
            'beautician'
        ]);
    }

    // Relationship to customer
    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    // Scope for filtering by status
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    // Scope for pending invoices
    public function scopePending($query)
    {
        return $query->where('status', 'PENDING');
    }

    // Scope for paid invoices
    public function scopePaid($query)
    {
        return $query->where('status', 'PAID');
    }

    // Generate unique invoice number
    public static function generateInvoiceNumber()
    {
        $lastInvoice = self::latest('id')->first();
        $number = $lastInvoice ? (int)substr($lastInvoice->invoice_number, 4) + 1 : 1;
        return 'INV-' . str_pad($number, 6, '0', STR_PAD_LEFT);
    }

    // Get formatted total
    public function getFormattedTotalAttribute()
    {
        return number_format((float)$this->total, 2);
    }

    // Check if invoice is overdue (more than 30 days)
    public function getIsOverdueAttribute()
    {
        return $this->status === 'PENDING' && $this->created_at->diffInDays(now()) > 30;
    }
}
