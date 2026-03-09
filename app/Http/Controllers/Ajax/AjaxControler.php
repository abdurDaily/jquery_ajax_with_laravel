<?php

namespace App\Http\Controllers\Ajax;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class AjaxControler extends Controller
{
    public function index()
    {
        $users = User::latest()->get();
        return view('welcome', compact('users'));
    }

    public function store(Request $request)
    {
        // dd($request->all()); // Debugging: Check the incoming request data
        $data = $request->validate([
            'name'     => 'required',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:6',
        ]);

        $user           = new User();
        $user->name     = $data['name'];
        $user->email    = $data['email'];
        $user->password = bcrypt($data['password']);
        $user->save();

        return response()->json([
            'status'  => 'success',
            'message' => 'User created successfully',
            'data'    => $user
        ]);
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        // dd($request->all()); // Debugging: Check the incoming request data
        $data = $request->validate([
            'name'     => 'required',
            'email'    => 'required|email',
            'password' => 'nullable|min:6',  // changed from required to nullable
        ]);

        // Only update password if provided
        if (!empty($data['password'])) {
            $user->password = bcrypt($data['password']);
        }

        $user->name  = $data['name'];
        $user->email = $data['email'];

        if (!empty($data['password'])) {
            $user->password = bcrypt($data['password']);
        }

        $user->save();

        return response()->json([
            'status'  => 'success',
            'message' => 'User updated successfully',
            'data'    => $user
        ]);
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        // dd($user); // Debugging: Check the user object before deletion
        return response()->json([
            'status' => 'success',
            'message' => 'User deleted successfully',
        ]);
    }
}
