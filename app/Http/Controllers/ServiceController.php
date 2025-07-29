<?php
namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceController extends Controller
{
    use SoftDeletes;

    public function index()
    {
        return Service::all();
    }

    public function store(Request $request)
    {
        $service = Service::create($request->all());
        return response()->json($service, 201);
    }

    public function update(Request $request, Service $service)
    {
        $service->update($request->all());
        return response()->json($service);
    }

    public function destroy(Service $service)
    {
        $service->delete();
        return response()->json(['message' => 'Service soft deleted']);
    }
}
