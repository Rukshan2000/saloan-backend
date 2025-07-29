<?php
namespace App\Http\Controllers;

use App\Models\AppointmentService;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\SoftDeletes;


class AppointmentServiceController extends Controller
{
    use SoftDeletes;

    public function index()
    {
        return AppointmentService::all();
    }

    public function store(Request $request)
    {
        $as = AppointmentService::create($request->all());
        return response()->json($as, 201);
    }

    public function update(Request $request, AppointmentService $appointmentService)
    {
        $appointmentService->update($request->all());
        return response()->json($appointmentService);
    }

    public function destroy(AppointmentService $appointmentService)
    {
        $appointmentService->delete();
        return response()->json(['message' => 'AppointmentService soft deleted']);
    }

    /**
     * Get available time slots for a beautician and service on a given date
     */
    public function getAvailableTimeSlots(Request $request)
    {
        $beauticianId = $request->input('beautician_id');
        $totalDuration = $request->input('total_duration');
        $date = $request->input('date');

        $slots = AppointmentService::getAvailableTimeSlots($beauticianId, $totalDuration, $date);
        return response()->json($slots);
    }
}
