<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    use SoftDeletes;

    public function index()
    {
        return User::all();
    }

    public function store(Request $request)
    {
        Log::info('Attempting to create user', ['data' => $request->all()]);
        $data = $request->only(['name', 'email', 'password', 'branch_id']);
        $data['role'] = $request->input('role_id');
        $user = User::create($data);
        Log::info('User created', ['user' => $user]);
        return response()->json($user, 201);
    }

    public function update(Request $request, User $user)
    {
        $data = $request->only(['name', 'email', 'password', 'branch_id']);
        $data['role'] = $request->input('role_id');
        $user->update($data);
        return response()->json($user);
    }

    public function destroy(User $user)
    {
        $user->delete();
        return response()->json(['message' => 'User soft deleted']);
    }
}
