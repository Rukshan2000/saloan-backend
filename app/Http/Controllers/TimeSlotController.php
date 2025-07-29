<?php
namespace App\Http\Controllers;

use App\Models\TimeSlot;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\SoftDeletes;

class TimeSlotController extends Controller
{
    use SoftDeletes;

    public function index()
    {
        return TimeSlot::all();
    }

    public function store(Request $request)
    {
        $ts = TimeSlot::create($request->all());
        return response()->json($ts, 201);
    }

    public function update(Request $request, TimeSlot $timeSlot)
    {
        $timeSlot->update($request->all());
        return response()->json($timeSlot);
    }

    public function destroy(TimeSlot $timeSlot)
    {
        $timeSlot->delete();
        return response()->json(['message' => 'TimeSlot soft deleted']);
    }
}
