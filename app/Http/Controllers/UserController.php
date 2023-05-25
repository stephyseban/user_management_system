<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function userDashboard()
    {
        $userCount = User::where('deleted_at',null)->where('role_id',config('common.role.user'))->count();
        $role = config('common.role.user');
        return view('user.dashboard',compact('userCount','role'));
    }
}
