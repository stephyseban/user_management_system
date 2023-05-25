<?php

namespace App\Repository;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminRepository
{

    public function userStore($data)
    {
        $user = new User();
        $user->name = $data['name'] ? $data['name'] : '';
        $user->email = $data['email'] ? $data['email'] : '';
        $user->password = Hash::make($data['password']);
        $user->role_id = config('common.role.user');
        $user->save();
        return $user;
    }

    public function userUpdate($data)
    {
        $userData = User::findOrFail($data['id']);
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
        return User::where("id", "=", $data['id'])->update($update_data);
        
    }
}
