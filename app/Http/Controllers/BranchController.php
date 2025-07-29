<?php
namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\SoftDeletes;

class BranchController extends Controller
{
    use SoftDeletes;

    public function index()
    {
        return Branch::all();
    }

    public function store(Request $request)
    {
        $branch = Branch::create($request->all());
        return response()->json($branch, 201);
    }

    public function update(Request $request, Branch $branch)
    {
        $branch->update($request->all());
        return response()->json($branch);
    }

    public function destroy(Branch $branch)
    {
        $branch->delete();
        return response()->json(['message' => 'Branch soft deleted']);
    }
}
