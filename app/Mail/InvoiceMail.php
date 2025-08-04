<?php

namespace App\Mail;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    public $invoice;
    public $customer;
    public $appointment;
    public $services;
    public $branch;
    public $beautician;

    /**
     * Create a new message instance.
     */
    public function __construct(Invoice $invoice)
    {
        $this->invoice = $invoice;
        $this->customer = $invoice->customer;
        $this->appointment = $invoice->appointment;
        $this->services = $invoice->appointment->services;
        $this->branch = $invoice->appointment->branch;
        $this->beautician = $invoice->appointment->beautician;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Invoice ' . $this->invoice->invoice_number . ' - Salon Services')
                    ->view('emails.invoice')
                    ->with([
                        'invoice' => $this->invoice,
                        'customer' => $this->customer,
                        'appointment' => $this->appointment,
                        'services' => $this->services,
                        'branch' => $this->branch,
                        'beautician' => $this->beautician,
                    ]);
    }
}
