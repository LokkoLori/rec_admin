<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class AdminController extends Controller
{

    // List roles
    public function rolesIndex()
    {
        $roles = Role::all(); // Retrieve all roles from the database
        return view('admin.roles', compact('roles'));
    }

    // Create a new role
    public function createRole(Request $request)
    {
        $role = Role::create(['name' => $request->name]);
        return redirect()->back()->with('success', 'Role created successfully.');
    }

    public function users()
    {
        $users = User::all();
        $roles = Role::all(); // Szerepkörök lekérése
        return view('admin.users', compact('users', 'roles'));
    }

    public function assignRole(Request $request, User $user)
    {
        $user->syncRoles($request->roles); // Szerepkörök hozzárendelése a felhasználóhoz
        return redirect()->back()->with('success', 'Szerepkörök frissítve.');
    }
}
