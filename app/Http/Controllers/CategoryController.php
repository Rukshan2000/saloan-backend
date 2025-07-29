<?php
namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\SoftDeletes;

class CategoryController extends Controller
{
    use SoftDeletes;

    public function index()
    {
        return Category::all();
    }

    public function store(Request $request)
    {
        $category = Category::create($request->all());
        return response()->json($category, 201);
    }

    public function update(Request $request, Category $category)
    {
        $category->update($request->all());
        return response()->json($category);
    }

    public function destroy(Category $category)
    {
        $category->delete();
        return response()->json(['message' => 'Category soft deleted']);
    }
}
