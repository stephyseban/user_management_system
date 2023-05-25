<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserUpdateRequest;
use App\Models\User;
use App\Repository\UserRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    protected $userRepo;
    public function __construct(UserRepository $userRepo)
    {
        $this->userRepo = $userRepo;
    }
    public function userDashboard()
    {
        $userCount = User::where('deleted_at',null)->where('role_id',config('common.role.user'))->count();
        $role = config('common.role.user');
        return view('user.dashboard',compact('userCount','role'));
    }

    public function userindex()
    {
        $users = User::where('deleted_at', null)->where('role_id', config('common.role.user'))->where('status', 0)->get();
        return view('user.users.list', compact('users'));
    }
    public function profileEdit()
    {
        $data = User::where('id',Auth::user()->id)->first();
        return view('user.profile-edit', compact('data'));
    }

    public function profileUpdate(UserUpdateRequest $request)
    {
        try {
            $userUpdate = $this->userRepo->userUpdate($request->all());
            if ($userUpdate) {
                return redirect()->route('profile.edit')->with('message', 'User Updated successfully!');
            }
        } catch (\Exception $e) {
            report($e);
            return redirect()->route('profile.edit')->with('error', $e->getMessage());
        }
    }
}
