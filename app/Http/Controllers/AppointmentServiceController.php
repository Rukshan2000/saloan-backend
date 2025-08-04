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

    /**
     * Find the best available beautician for a set of services
     */
    public function findBestBeautician(Request $request)
    {
        $serviceIds = $request->input('service_ids', []);
        $date = $request->input('date');
        $branchId = $request->input('branch_id');

        if (empty($serviceIds) || !$date) {
            return response()->json(['error' => 'service_ids and date are required'], 400);
        }

        $result = AppointmentService::findBestAvailableBeautician($serviceIds, $date, $branchId);
        
        if (!$result) {
            return response()->json(['message' => 'No available beautician found for the requested services and date'], 404);
        }

        return response()->json($result);
    }

    /**
     * Get all available beauticians for a set of services
     */
    public function getAvailableBeauticians(Request $request)
    {
        $serviceIds = $request->input('service_ids', []);
        $date = $request->input('date');
        $branchId = $request->input('branch_id');

        if (empty($serviceIds) || !$date) {
            return response()->json(['error' => 'service_ids and date are required'], 400);
        }

        $beauticians = AppointmentService::getAvailableBeauticiansForServices($serviceIds, $date, $branchId);
        
        return response()->json([
            'date' => $date,
            'available_beauticians' => $beauticians,
            'count' => $beauticians->count()
        ]);
    }

    /**
     * Validate appointment booking request
     */
    public function validateBooking(Request $request)
    {
        try {
            $serviceIds = $request->input('service_ids', []);
            $date = $request->input('date');
            $branchId = $request->input('branch_id');
            $beauticianId = $request->input('beautician_id');
            
            $validation = [
                'is_valid' => true,
                'errors' => [],
                'warnings' => []
            ];

            // Check if services exist
            if (empty($serviceIds)) {
                $validation['errors'][] = 'At least one service must be selected';
                $validation['is_valid'] = false;
            } else {
                $services = \App\Models\Service::whereIn('id', $serviceIds)->get();
                if ($services->count() !== count($serviceIds)) {
                    $validation['errors'][] = 'One or more selected services do not exist';
                    $validation['is_valid'] = false;
                }
            }

            // Check date validity
            if (!$date) {
                $validation['errors'][] = 'Date is required';
                $validation['is_valid'] = false;
            } elseif (strtotime($date) < strtotime('today')) {
                $validation['errors'][] = 'Cannot book appointments in the past';
                $validation['is_valid'] = false;
            }

            // Check if specific beautician is requested
            if ($beauticianId) {
                $beautician = \App\Models\User::find($beauticianId);
                if (!$beautician) {
                    $validation['errors'][] = 'Specified beautician does not exist';
                    $validation['is_valid'] = false;
                } else {
                    // Check if beautician can perform all services
                    $beauticianServices = \App\Models\ServiceBeautician::where('beautician_id', $beauticianId)
                        ->whereIn('service_id', $serviceIds)
                        ->count();
                    
                    if ($beauticianServices !== count($serviceIds)) {
                        $validation['errors'][] = 'Specified beautician cannot perform all selected services';
                        $validation['is_valid'] = false;
                    }
                }
            }

            // Check branch validity
            if ($branchId) {
                $branch = \App\Models\Branch::find($branchId);
                if (!$branch) {
                    $validation['errors'][] = 'Specified branch does not exist';
                    $validation['is_valid'] = false;
                }
            }

            return response()->json($validation);

        } catch (\Exception $e) {
            return response()->json([
                'is_valid' => false,
                'errors' => ['Validation failed: ' . $e->getMessage()]
            ], 500);
        }
    }
}
