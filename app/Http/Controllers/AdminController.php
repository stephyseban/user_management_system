<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function dashboard()
    {
        $users = User::where('status',0)->count();
        return view('admin.dashboard',compact('users'));
    } 
    public function user()
    {
        $users = User::where('deleted_at',null)->where('status',0)->get();
        return view('admin.user.list',compact('users'));
    }
    public function userAdd()
    {
        return view('admin.user.add');
    }
}
