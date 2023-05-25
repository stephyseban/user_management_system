<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserStoreRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Models\User;
use App\Repository\AdminRepository;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    protected $adminRepo;
    public function __construct(AdminRepository $adminRepo)
    {
        $this->adminRepo = $adminRepo;
    }
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
    /**
     * User list.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function userindex()
    {
        $users = User::where('deleted_at', null)->where('role_id', config('common.role.user'))->where('status', 0)->get();
        return view('admin.user.list', compact('users'));
    }
    /**
     * User Add.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function userAdd()
    {
        return view('admin.user.add');
    }
    /**
     * User Detail store.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function userStore(UserStoreRequest $request)
    {
        try {
            $userStore = $this->adminRepo->userStore($request->all());
            if ($userStore) {
                return redirect()->route('user.list')->with('message', 'User Added successfully!');
            }
        } catch (\Exception $e) {
            report($e);
            return redirect()->route('user.add')->with('error', $e->getMessage());
        }
    }
    /**
     * User Delete.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $update_data = array(
            'status' => 1,
            'deleted_at' => now(),
        );
        User::where("id", "=", $id)->update($update_data);
        $data = ['status' => true, 'msg' => 'success'];
        return response()->json($data);
    }
    /**
     * Edit page.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function userEdit($id)
    {
        $data = User::findOrfail($id);
        return view('admin.user.edit', compact('data'));
    }

    /**
     * user detail update.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function userUpdate(UserUpdateRequest $request, $id)
    {
        try {
            $userUpdate = $this->adminRepo->userUpdate($request->all());
            if ($userUpdate) {
                return redirect()->route('user.list')->with('message', 'User Updated successfully!');
            }
        } catch (\Exception $e) {
            report($e);
            return redirect()->route('user.edit')->with('error', $e->getMessage());
        }
    }
}
