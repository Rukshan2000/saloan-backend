<?php
namespace App\Http\Controllers;

use App\Models\ServiceBeautician;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceBeauticianController extends Controller
{
    use SoftDeletes;

    public function index()
    {
        return ServiceBeautician::all();
    }

    public function store(Request $request)
    {
        $sb = ServiceBeautician::create($request->all());
        return response()->json($sb, 201);
    }

    public function update(Request $request, ServiceBeautician $serviceBeautician)
    {
        $serviceBeautician->update($request->all());
        return response()->json($serviceBeautician);
    }

    public function destroy(ServiceBeautician $serviceBeautician)
    {
        $serviceBeautician->delete();
        return response()->json(['message' => 'ServiceBeautician soft deleted']);
    }
}
