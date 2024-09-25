<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class AdminUserController extends Controller
{
    /**
     * Display the user management page.
     */
    public function index()
    {
        $users = User::all();
        return view('admin.user', compact('users'));
    }
}
