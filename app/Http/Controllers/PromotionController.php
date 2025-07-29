<?php
namespace App\Http\Controllers;

use App\Models\Promotion;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\SoftDeletes;

class PromotionController extends Controller
{
    use SoftDeletes;

    public function index()
    {
        return Promotion::all();
    }

    public function store(Request $request)
    {
        $promotion = Promotion::create($request->all());
        return response()->json($promotion, 201);
    }

    public function update(Request $request, Promotion $promotion)
    {
        $promotion->update($request->all());
        return response()->json($promotion);
    }

    public function destroy(Promotion $promotion)
    {
        $promotion->delete();
        return response()->json(['message' => 'Promotion soft deleted']);
    }
}
