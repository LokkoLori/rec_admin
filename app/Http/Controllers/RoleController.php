<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::all(); // Retrieve all roles from the database
        return view('admin.roles.index', compact('roles'));
    }
}
