<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission; // Naya import add kiya
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('roles')->get();
        $roles = Role::all();
        return view('admin.pages.Access_Control.permissions', compact('users', 'roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6|confirmed',
            'role' => 'required'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $user->assignRole($request->role);

        return redirect()->back()->with('success', 'User Created Successfully!');
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email,'.$id,
            'role' => 'required'
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        $user->syncRoles($request->role);

        return redirect()->back()->with('success', 'User Updated Successfully!');
    }

    public function destroy($id)
    {
        User::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'User Deleted Successfully!');
    }

    // --- YE NAYE METHODS ADD KIYE HAIN ---

    /**
     * User ko specific permissions dene wala page dikhana
     */
   
        public function assignPermissions($id)
{
    $user = User::findOrFail($id);

    // Check karein ke kya user ke paas kam se kam ek role hai?
    if ($user->roles->isEmpty()) {
        return redirect()->route('access-control.permissions.index')
                         ->with('error', 'User do not have any role! Please assign role first by clicking on edit button. Thanks!');
    }

    $permissions = Permission::all();
    $userPermissions = $user->getPermissionNames()->toArray();

    return view('admin.pages.Access_Control.assign_permissions', compact('user', 'permissions', 'userPermissions'));
}
    

    /**
     * User ki permissions update karna
     */
    public function updatePermissions(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        // SyncPermissions purani sari permissions hata kar sirf nayi wali add kar deta hai
        $user->syncPermissions($request->permissions);

        return redirect()->route('access-control.permissions.index')->with('success', 'User Permissions Updated Successfully!');
    }
}