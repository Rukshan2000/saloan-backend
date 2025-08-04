<?php

// Example usage of the Invoice System
// This demonstrates how to use the enhanced Invoice functionality

require_once 'vendor/autoload.php';

use App\Models\Invoice;
use App\Models\Appointment;
use App\Mail\InvoiceMail;
use Illuminate\Support\Facades\Mail;

/**
 * Example 1: Creating an Invoice from an Appointment
 */
function createInvoiceFromAppointment($appointmentId, $sendEmail = true) {
    try {
        // Check if invoice already exists
        $existingInvoice = Invoice::where('appointment_id', $appointmentId)->first();
        if ($existingInvoice) {
            return [
                'success' => false,
                'message' => 'Invoice already exists for this appointment',
                'data' => $existingInvoice
            ];
        }

        // Get appointment with all related data
        $appointment = Appointment::with([
            'services.service',
            'customer',
            'branch'
        ])->findOrFail($appointmentId);

        // Calculate total from appointment services
        $total = $appointment->services->sum('price');

        // Create invoice
        $invoice = Invoice::create([
            'appointment_id' => $appointment->id,
            'customer_id' => $appointment->customer_id,
            'invoice_number' => Invoice::generateInvoiceNumber(),
            'status' => 'PENDING',
            'total' => $total
        ]);

        // Load relationships for response
        $invoice->load([
            'customer:id,name,email',
            'appointment' => function($query) {
                $query->with([
                    'services.service.category',
                    'branch:id,name,address,phone,email',
                    'beautician:id,name'
                ]);
            }
        ]);

        // Send email if requested
        if ($sendEmail && $invoice->customer->email) {
            Mail::to($invoice->customer->email)->send(new InvoiceMail($invoice));
        }

        return [
            'success' => true,
            'data' => $invoice,
            'message' => 'Invoice created successfully' . ($sendEmail ? ' and email sent' : '')
        ];

    } catch (\Exception $e) {
        return [
            'success' => false,
            'message' => 'Error creating invoice',
            'error' => $e->getMessage()
        ];
    }
}

/**
 * Example 2: Getting Invoice Statistics
 */
function getInvoiceStatistics() {
    return [
        'total_invoices' => Invoice::count(),
        'pending_invoices' => Invoice::pending()->count(),
        'paid_invoices' => Invoice::paid()->count(),
        'overdue_invoices' => Invoice::byStatus('OVERDUE')->count(),
        'cancelled_invoices' => Invoice::byStatus('CANCELLED')->count(),
        'total_revenue' => Invoice::paid()->sum('total'),
        'pending_revenue' => Invoice::pending()->sum('total'),
        'monthly_revenue' => Invoice::paid()
            ->whereMonth('updated_at', now()->month)
            ->whereYear('updated_at', now()->year)
            ->sum('total')
    ];
}

/**
 * Example 3: Filtering Invoices
 */
function getFilteredInvoices($filters = []) {
    $query = Invoice::with([
        'customer:id,name,email',
        'appointment' => function($query) {
            $query->with([
                'services.service.category',
                'branch:id,name,address,phone,email',
                'beautician:id,name'
            ]);
        }
    ]);

    // Apply filters
    if (isset($filters['status'])) {
        $query->byStatus($filters['status']);
    }

    if (isset($filters['customer_id'])) {
        $query->where('customer_id', $filters['customer_id']);
    }

    if (isset($filters['date_from'])) {
        $query->whereDate('created_at', '>=', $filters['date_from']);
    }

    if (isset($filters['date_to'])) {
        $query->whereDate('created_at', '<=', $filters['date_to']);
    }

    return $query->orderBy('created_at', 'desc')->paginate(15);
}

/**
 * Example 4: Updating Invoice Status
 */
function updateInvoiceStatus($invoiceId, $newStatus) {
    try {
        $validStatuses = ['PENDING', 'PAID', 'OVERDUE', 'CANCELLED'];
        
        if (!in_array($newStatus, $validStatuses)) {
            return [
                'success' => false,
                'message' => 'Invalid status. Must be one of: ' . implode(', ', $validStatuses)
            ];
        }

        $invoice = Invoice::findOrFail($invoiceId);
        $oldStatus = $invoice->status;
        $invoice->update(['status' => $newStatus]);

        return [
            'success' => true,
            'data' => $invoice,
            'message' => "Invoice status updated from {$oldStatus} to {$newStatus}"
        ];

    } catch (\Exception $e) {
        return [
            'success' => false,
            'message' => 'Error updating invoice status',
            'error' => $e->getMessage()
        ];
    }
}

/**
 * Example 5: Sending Invoice Email
 */
function sendInvoiceEmail($invoiceId) {
    try {
        $invoice = Invoice::with([
            'customer:id,name,email',
            'appointment' => function($query) {
                $query->with([
                    'services.service.category',
                    'branch:id,name,address,phone,email',
                    'beautician:id,name'
                ]);
            }
        ])->findOrFail($invoiceId);

        if (!$invoice->customer->email) {
            return [
                'success' => false,
                'message' => 'Customer email not found'
            ];
        }

        Mail::to($invoice->customer->email)->send(new InvoiceMail($invoice));

        return [
            'success' => true,
            'message' => 'Invoice email sent successfully to ' . $invoice->customer->email
        ];

    } catch (\Exception $e) {
        return [
            'success' => false,
            'message' => 'Error sending invoice email',
            'error' => $e->getMessage()
        ];
    }
}

// Usage Examples:
/*
// Create invoice from appointment and send email
$result = createInvoiceFromAppointment(1, true);

// Get invoice statistics
$stats = getInvoiceStatistics();

// Get filtered invoices
$invoices = getFilteredInvoices([
    'status' => 'PENDING',
    'customer_id' => 2,
    'date_from' => '2025-08-01'
]);

// Update invoice status
$result = updateInvoiceStatus(1, 'PAID');

// Send invoice email
$result = sendInvoiceEmail(1);
*/
