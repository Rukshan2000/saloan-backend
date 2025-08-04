<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// Test route to check if invoice email view works
Route::get('/test-invoice-view', function () {
    // Create dummy data
    $invoice = (object) [
        'invoice_number' => 'INV-000001',
        'status' => 'PENDING',
        'created_at' => now(),
        'formatted_total' => '150.00'
    ];
    
    $customer = (object) [
        'name' => 'Test Customer',
        'email' => 'test@example.com'
    ];
    
    $appointment = (object) [
        'receipt_number' => 'RCP-001',
        'date' => '2025-08-05',
        'start_time' => '10:00:00',
        'end_time' => '11:30:00'
    ];
    
    $branch = (object) [
        'name' => 'Main Branch',
        'address' => '123 Beauty Street',
        'phone' => '555-0123',
        'email' => 'info@salon.com'
    ];
    
    $beautician = (object) [
        'name' => 'Jane Smith'
    ];
    
    $services = collect([
        (object) [
            'service' => (object) [
                'name' => 'Haircut & Style',
                'description' => 'Professional haircut with styling',
                'category' => (object) ['name' => 'Hair Services']
            ],
            'duration' => 60,
            'price' => 75.00
        ],
        (object) [
            'service' => (object) [
                'name' => 'Manicure',
                'description' => 'Classic manicure',
                'category' => (object) ['name' => 'Nail Services']
            ],
            'duration' => 30,
            'price' => 75.00
        ]
    ]);
    
    return view('emails.invoice', compact('invoice', 'customer', 'appointment', 'branch', 'beautician', 'services'));
});
