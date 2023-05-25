<?php

namespace App\Repository;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserRepository
{
    public function userUpdate($data)
    {
        
        $userData = User::findOrFail(Auth::user()->id);
        if ($data['password']) {
            $password = Hash::make($data['password']);
        } else {
            $password =   $userData['password'];
        }
        $update_data = array(
            'name' => $data['name'] ? $data['name'] : '',
            'email' => $data['email'] ? $data['email'] : '',
            'password' =>   $password,
            'role_id' => config('common.role.user'),
            'updated_at' => now(),
        );
        return User::where("id", "=", Auth::user()->id)->update($update_data);
    }
}
