<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
            border-radius: 10px 10px 0 0;
        }
        .invoice-details {
            background: #f8f9fa;
            padding: 20px;
            border: 1px solid #dee2e6;
        }
        .customer-info {
            margin: 20px 0;
            padding: 15px;
            background: white;
            border-left: 4px solid #667eea;
        }
        .services-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            background: white;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .services-table th,
        .services-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .services-table th {
            background: #667eea;
            color: white;
        }
        .services-table tr:hover {
            background: #f5f5f5;
        }
        .total-section {
            background: #e9ecef;
            padding: 20px;
            border-radius: 0 0 10px 10px;
            text-align: right;
        }
        .total-amount {
            font-size: 24px;
            font-weight: bold;
            color: #667eea;
        }
        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        .status-paid {
            background: #d4edda;
            color: #155724;
        }
        .status-overdue {
            background: #f8d7da;
            color: #721c24;
        }
        .status-cancelled {
            background: #f1f3f4;
            color: #5f6368;
        }
        .footer {
            margin-top: 30px;
            padding: 20px;
            background: #f8f9fa;
            text-align: center;
            font-size: 14px;
            color: #6c757d;
        }
        .appointment-info {
            background: white;
            padding: 15px;
            margin: 15px 0;
            border-radius: 5px;
            border: 1px solid #dee2e6;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin: 8px 0;
        }
        .info-label {
            font-weight: bold;
            color: #495057;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $branch->name ?? 'Salon' }}</h1>
        <p>Professional Beauty Services</p>
    </div>

    <div class="invoice-details">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h2>Invoice {{ $invoice->invoice_number }}</h2>
                <p><strong>Date:</strong> {{ $invoice->created_at->format('F d, Y') }}</p>
            </div>
            <div>
                <span class="status-badge status-{{ strtolower($invoice->status) }}">
                    {{ $invoice->status }}
                </span>
            </div>
        </div>
    </div>

    <div class="customer-info">
        <h3>Bill To:</h3>
        <p><strong>{{ $customer->name }}</strong></p>
        <p>{{ $customer->email }}</p>
    </div>

    <div class="appointment-info">
        <h3>Appointment Details</h3>
        <div class="info-row">
            <span class="info-label">Receipt Number:</span>
            <span>{{ $appointment->receipt_number }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Date:</span>
            <span>{{ \Carbon\Carbon::parse($appointment->date)->format('F d, Y') }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Time:</span>
            <span>{{ \Carbon\Carbon::parse($appointment->start_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($appointment->end_time)->format('g:i A') }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Beautician:</span>
            <span>{{ $beautician->name ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Branch:</span>
            <span>{{ $branch->name ?? 'N/A' }}</span>
        </div>
        @if($branch && $branch->address)
        <div class="info-row">
            <span class="info-label">Location:</span>
            <span>{{ $branch->address }}</span>
        </div>
        @endif
    </div>

    <table class="services-table">
        <thead>
            <tr>
                <th>Service</th>
                <th>Category</th>
                <th>Duration</th>
                <th>Price</th>
            </tr>
        </thead>
        <tbody>
            @foreach($services as $appointmentService)
            <tr>
                <td>
                    <strong>{{ $appointmentService->service->name }}</strong>
                    @if($appointmentService->service->description)
                    <br><small style="color: #6c757d;">{{ $appointmentService->service->description }}</small>
                    @endif
                </td>
                <td>{{ $appointmentService->service->category->name ?? 'N/A' }}</td>
                <td>{{ $appointmentService->duration }} minutes</td>
                <td>${{ number_format($appointmentService->price, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="total-section">
        <div style="margin-bottom: 10px;">
            <strong>Subtotal: ${{ $invoice->formatted_total }}</strong>
        </div>
        <div class="total-amount">
            Total: ${{ $invoice->formatted_total }}
        </div>
    </div>

    <div class="footer">
        <p>Thank you for choosing our salon services!</p>
        <p>If you have any questions about this invoice, please contact us.</p>
        @if(($branch->phone ?? $branch->contact ?? false))
        <p>Phone: {{ $branch->phone ?? $branch->contact }}</p>
        @endif
        @if(($branch->email ?? false))
        <p>Email: {{ $branch->email }}</p>
        @endif
    </div>
</body>
</html>
