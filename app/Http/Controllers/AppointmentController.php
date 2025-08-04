<?php
namespace App\Http\Controllers;

use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\SoftDeletes;

class AppointmentController extends Controller
{
    use SoftDeletes;

        public function index()
        {
            $appointments = Appointment::with(['services', 'customer'])->get();

            $result = $appointments->map(function ($appointment) {
                // Get service_ids and services array
                $serviceIds = $appointment->services->pluck('service_id')->map(fn($id) => (string)$id)->toArray();
                $services = $appointment->services->map(function ($service) {
                    return [
                        'id' => (int)$service->service_id,
                        'price' => (string)$service->price,
                        'duration' => (int)$service->duration,
                    ];
                })->toArray();

                return [
                    'id' => $appointment->id,
                    'customer_id' => $appointment->customer_id,
                    'beautician_id' => (string)$appointment->beautician_id,
                    'branch_id' => (string)$appointment->branch_id,
                    'time_slot_id' => (string)($appointment->time_slot_id ?? ''),
                    'date' => $appointment->date,
                    'status' => $appointment->status,
                    'name' => $appointment->customer ? $appointment->customer->name : null,
                    'email' => $appointment->customer ? $appointment->customer->email : null,
                    'service_ids' => $serviceIds,
                    'services' => $services,
                ];
            });

            return response()->json($result);
        }

    public function store(Request $request)
    {
        try {
            // Handle customer creation/finding if name and email are provided
            $customerId = $request->input('customer_id');
            
            if ($request->has('name') && $request->has('email') && !$customerId) {
                // Find or create customer
                $customer = \App\Models\User::firstOrCreate(
                    ['email' => $request->input('email')],
                    [
                        'name' => $request->input('name'),
                        'email' => $request->input('email'),
                        'password' => bcrypt('defaultpassword'), // Set a default password
                        'role_id' => 1 // CUSTOMER role
                    ]
                );
                $customerId = $customer->id;
            }

            // Extract appointment data (exclude service_ids, services, name, email)
            $appointmentData = $request->except(['service_ids', 'services', 'name', 'email']);
            $appointmentData['customer_id'] = $customerId;
            
            // Set default status if not provided
            if (!isset($appointmentData['status'])) {
                $appointmentData['status'] = 'SCHEDULED';
            }
            
            // Generate receipt number if not provided
            if (!isset($appointmentData['receipt_number'])) {
                $appointmentData['receipt_number'] = 'R' . time() . rand(100, 999);
            }

            $appointment = Appointment::create($appointmentData);

            // Save appointment services using 'services' array (with price and duration)
            if ($request->has('services') && is_array($request->services)) {
                foreach ($request->services as $service) {
                    \App\Models\AppointmentService::create([
                        'appointment_id' => $appointment->id,
                        'service_id' => $service['id'],
                        'price' => $service['price'],
                        'duration' => $service['duration'],
                    ]);
                }
            } elseif ($request->has('service_ids')) {
                // Create appointment services from service_ids
                $services = \App\Models\Service::whereIn('id', $request->service_ids)->get();
                foreach ($services as $service) {
                    \App\Models\AppointmentService::create([
                        'appointment_id' => $appointment->id,
                        'service_id' => $service->id,
                        'price' => $service->price,
                        'duration' => $service->duration,
                    ]);
                }
            }

            return response()->json($appointment, 201);
        } catch (\Exception $e) {
            \Log::error('Appointment creation failed: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['error' => 'Appointment creation failed', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Create appointment with intelligent beautician assignment
     */
    public function storeWithSmartBooking(Request $request)
    {
        try {
            $serviceIds = $request->input('service_ids', []);
            $date = $request->input('date');
            $branchId = $request->input('branch_id');
            $customerId = $request->input('customer_id');
            
            if (empty($serviceIds) || !$date || !$customerId) {
                return response()->json(['error' => 'service_ids, date, and customer_id are required'], 400);
            }

            // Find the best available beautician
            $bestSlot = \App\Models\AppointmentService::findBestAvailableBeautician($serviceIds, $date, $branchId);
            
            if (!$bestSlot) {
                return response()->json(['error' => 'No available beautician found for the requested services and date'], 404);
            }

            // Create the appointment
            $appointment = Appointment::create([
                'customer_id' => $customerId,
                'beautician_id' => $bestSlot['beautician_id'],
                'branch_id' => $branchId,
                'date' => $date,
                'start_time' => $bestSlot['start_time'],
                'end_time' => $bestSlot['end_time'],
                'status' => 'SCHEDULED',
                'receipt_number' => 'R' . time() . rand(100, 999)
            ]);

            // Save appointment services
            if ($request->has('services') && is_array($request->services)) {
                foreach ($request->services as $service) {
                    \App\Models\AppointmentService::create([
                        'appointment_id' => $appointment->id,
                        'service_id' => $service['id'],
                        'price' => $service['price'],
                        'duration' => $service['duration'],
                    ]);
                }
            } else {
                // Create appointment services from service_ids
                $services = \App\Models\Service::whereIn('id', $serviceIds)->get();
                foreach ($services as $service) {
                    \App\Models\AppointmentService::create([
                        'appointment_id' => $appointment->id,
                        'service_id' => $service->id,
                        'price' => $service->price,
                        'duration' => $service->duration,
                    ]);
                }
            }

            // Return appointment with booking details
            return response()->json([
                'appointment' => $appointment,
                'booking_details' => $bestSlot,
                'message' => 'Appointment created successfully with smart booking'
            ], 201);

        } catch (\Exception $e) {
            \Log::error('Smart appointment creation failed: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['error' => 'Smart appointment creation failed', 'message' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, Appointment $appointment)
    {
        $appointment->update($request->all());
        return response()->json($appointment);
    }

    public function destroy(Appointment $appointment)
    {
        $appointment->delete();
        return response()->json(['message' => 'Appointment soft deleted']);
    }
}
