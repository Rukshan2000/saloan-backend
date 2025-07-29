<?php
namespace App\Http\Controllers;

use App\Models\BeauticianAvailability;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\SoftDeletes;

class BeauticianAvailabilityController extends Controller
{
    use SoftDeletes;

    public function index()
    {
        return BeauticianAvailability::all();
    }

    public function store(Request $request)
    {
        $ba = BeauticianAvailability::create($request->all());
        return response()->json($ba, 201);
    }

    public function update(Request $request, BeauticianAvailability $beauticianAvailability)
    {
        $beauticianAvailability->update($request->all());
        return response()->json($beauticianAvailability);
    }

    public function destroy(BeauticianAvailability $beauticianAvailability)
    {
        $beauticianAvailability->delete();
        return response()->json(['message' => 'BeauticianAvailability soft deleted']);
    }
}
