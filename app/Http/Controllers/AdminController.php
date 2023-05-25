<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    /**
     * Dashboard.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function dashboard()
    {
        $users = User::where('deleted_at', null)->where('role_id', config('common.role.user'))->count();
        return view('admin.dashboard', compact('users'));
    }
    public function userindex()
    {
        $users = User::where('deleted_at', null)->where('role_id', config('common.role.user'))->where('status', 0)->get();
        return view('admin.user.list', compact('users'));
    }
    public function userAdd()
    {
        return view('admin.user.add');
    }
    public function userStore(Request $request)
    {

        $validatedData =  $request->validate([
            'name' => 'required|regex:/^[a-zA-Z]+$/u|max:255',
            'email' => 'required|email|unique:users,email,' . $request->email,
            'password' => 'min:6|required_with:password_confirmation|same:password_confirmation',
            'password_confirmation' => 'min:6'
        ]);
        if ($validatedData) {
            try {
                $data = $request->all();
                $user = new User();
                $user->name = $data['name'] ? $data['name'] : '';
                $user->email = $data['email'] ? $data['email'] : '';
                $user->password = Hash::make($data['password']);
                $user->role_id = config('common.role.user');
                $user->save();
                return redirect()->route('user.list')->with('message', 'User Added successfully!');
            } catch (\Exception $e) {
                report($e);
                return redirect()->route('user.add')->with('error', $e->getMessage());
            }
        }
    }

    public function destroy($id)
    {
        $update_data = array(
            'status' => 1,
            'deleted_at' => now(),
        );
        User::where("id", "=", $id)->update($update_data);
        $data = ['status' => true ,'msg'=>'success'];
        return response()->json($data);
    }

    public function userEdit($id)
    {
        $data = User::findOrfail($id);
        return view('admin.user.edit',compact('data'));
    }

    
    public function userUpdate(Request $request)
    {
        $data = $request->all(); 
        if($data['password'])
        {
            $validatedData =  $request->validate([
                'name' => 'required|regex:/^[a-zA-Z]+$/u|max:255',
                'email' => 'required|email|unique:users,email,' . $request->email,
                'password' => 'min:6|required_with:password_confirmation|same:password_confirmation',
                'password_confirmation' => 'min:6'
            ]);
        }
        else{
            $validatedData =  $request->validate([
                'name' => 'required|regex:/^[a-zA-Z]+$/u|max:255',
                'email' => 'required|email|unique:users,email,' . $request->email,
            ]);
        }
      if($validatedData){
        $userData = User::findOrFail($data['id']);
        dd($userData);
        
        $update_data = array(
        'name' => $data['name']?$data['name']:'',
        'email' => $data['email']?$data['email']:'',
        'password' => Hash::make($data['password']),
        'role_id' => config('common.role.user'),
        'updated_at' => now(),
        );
        User::where("id", "=",$data['id'])->update($update_data);
        return redirect()->route('designation.index')->with('create', 'designation Updated successfully!');
    }
}
}