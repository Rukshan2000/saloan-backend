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
            // Extract appointment data (exclude service_ids, services, name, email)
            $appointmentData = $request->except(['service_ids', 'services', 'name', 'email']);
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
            }

            return response()->json($appointment, 201);
        } catch (\Exception $e) {
            \Log::error('Appointment creation failed: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['error' => 'Appointment creation failed', 'message' => $e->getMessage()], 500);
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
