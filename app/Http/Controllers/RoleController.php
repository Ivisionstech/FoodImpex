<?php

namespace App\Http\Controllers;

// Purana model hata kar ye wala use karein
use Spatie\Permission\Models\Role; 
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index() {
        $roles = Role::all();
        // Aapka purana view path
        return view('admin.pages.Access_Control.roles', compact('roles'));
    }

    public function store(Request $request) {
        $request->validate([
            'name' => 'required|unique:roles,name'
        ]);

        // Spatie model ko guard_name chahiye hota hai
        Role::create([
            'name' => $request->name,
            'guard_name' => 'web' // Ye line lazmi hai
        ]);

        return redirect()->back()->with('success', 'Role added successfully!');
    }

    public function update(Request $request, $id) {
        // FindOrFail use karein taake model binding ka issue na ho
        $role = Role::findOrFail($id);

        $request->validate([
            'name' => 'required|unique:roles,name,' . $role->id
        ]);

        $role->update([
            'name' => $request->name,
            // guard_name pehle se set hota hai, update ki zaroorat nahi
        ]);

        return redirect()->back()->with('success', 'Role updated successfully!');
    }

    public function destroy($id) {
        $role = Role::findOrFail($id);
        $role->delete();
        return redirect()->back()->with('success', 'Role deleted successfully!');
    }
}