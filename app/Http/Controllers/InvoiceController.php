<?php
namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Appointment;
use App\Mail\InvoiceMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InvoiceController extends Controller
{
    /**
     * Display a listing of invoices with relationships
     */
    public function index(Request $request)
    {
        try {
            $query = Invoice::with([
                'customer:id,name,email',
                'appointment' => function($query) {
                    $query->with([
                        'services.service.category',
                        'branch',
                        'beautician:id,name'
                    ]);
                }
            ]);

            // Filter by status if provided
            if ($request->has('status')) {
                $query->byStatus($request->status);
            }

            // Filter by customer if provided
            if ($request->has('customer_id')) {
                $query->where('customer_id', $request->customer_id);
            }

            // Filter by date range
            if ($request->has('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }
            if ($request->has('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            // Check for overdue invoices and update status
            $this->updateOverdueInvoices();

            $invoices = $query->orderBy('created_at', 'desc')->paginate(15);

            return response()->json([
                'success' => true,
                'data' => $invoices,
                'message' => 'Invoices retrieved successfully'
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error fetching invoices: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching invoices',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show a specific invoice with full relationships
     */
    public function show($id)
    {
        try {
            $invoice = Invoice::with([
                'customer:id,name,email',
                'appointment' => function($query) {
                    $query->with([
                        'services.service.category',
                        'branch',
                        'beautician:id,name'
                    ]);
                }
            ])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $invoice,
                'message' => 'Invoice retrieved successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Create invoice from appointment
     */
    public function createFromAppointment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'appointment_id' => 'required|exists:appointments,id',
            'send_email' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Check if invoice already exists for this appointment
            $existingInvoice = Invoice::where('appointment_id', $request->appointment_id)->first();
            if ($existingInvoice) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invoice already exists for this appointment',
                    'data' => $existingInvoice
                ], 409);
            }

            // Get appointment with all related data
            $appointment = Appointment::with([
                'services.service',
                'customer',
                'branch'
            ])->findOrFail($request->appointment_id);

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
                        'branch',
                        'beautician:id,name'
                    ]);
                }
            ]);

            // Send email if requested
            if ($request->get('send_email', true)) {
                $this->sendInvoiceEmail($invoice);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $invoice,
                'message' => 'Invoice created successfully' . ($request->get('send_email', true) ? ' and email sent' : '')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating invoice: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error creating invoice',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update invoice status
     */
    public function updateStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:PENDING,PAID,OVERDUE,CANCELLED'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $invoice = Invoice::findOrFail($id);
            $oldStatus = $invoice->status;
            $invoice->update(['status' => $request->status]);

            // Load relationships for response
            $invoice->load([
                'customer:id,name,email',
                'appointment.services.service.category'
            ]);

            return response()->json([
                'success' => true,
                'data' => $invoice,
                'message' => "Invoice status updated from {$oldStatus} to {$request->status}"
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating invoice status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send or resend invoice email
     */
    public function sendEmail($id)
    {
        try {
            $invoice = Invoice::with([
                'customer:id,name,email',
                'appointment' => function($query) {
                    $query->with([
                        'services.service.category',
                        'branch',
                        'beautician:id,name'
                    ]);
                }
            ])->findOrFail($id);

            $this->sendInvoiceEmail($invoice);

            return response()->json([
                'success' => true,
                'message' => 'Invoice email sent successfully to ' . $invoice->customer->email
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error sending invoice email: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error sending invoice email',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get invoice statistics
     */
    public function statistics()
    {
        try {
            $stats = [
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

            return response()->json([
                'success' => true,
                'data' => $stats,
                'message' => 'Invoice statistics retrieved successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete invoice (soft delete)
     */
    public function destroy($id)
    {
        try {
            $invoice = Invoice::findOrFail($id);
            
            // Check if invoice can be deleted (only if pending or cancelled)
            if (!in_array($invoice->status, ['PENDING', 'CANCELLED'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete a paid or overdue invoice'
                ], 400);
            }

            $invoice->delete();

            return response()->json([
                'success' => true,
                'message' => 'Invoice deleted successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting invoice',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Private method to send invoice email
     */
    private function sendInvoiceEmail($invoice)
    {
        // Ensure all relationships are loaded
        $invoice->load([
            'customer:id,name,email',
            'appointment' => function($query) {
                $query->with([
                    'services.service.category',
                    'branch',
                    'beautician:id,name'
                ]);
            }
        ]);

        if (!$invoice->customer->email) {
            throw new \Exception('Customer email not found');
        }

        Mail::to($invoice->customer->email)->send(new InvoiceMail($invoice));
    }

    /**
     * Update overdue invoices
     */
    private function updateOverdueInvoices()
    {
        Invoice::where('status', 'PENDING')
            ->where('created_at', '<', now()->subDays(30))
            ->update(['status' => 'OVERDUE']);
    }
}
