<?php

namespace App\Http\Controllers;

use App\Models\Matches;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index()
    {
        $totalUsers = User::count();
        $totalTeams = Team::count();
        $totalMatches = Matches::count();

        return view('admin.frontend.home', compact('totalUsers', 'totalTeams', 'totalMatches'));
    }

    public function allUsers()
    {
        $users = User::all();
        return view('admin.frontend.users.index', compact('users'));
    }
    public function editUser($id)
    {
        $user = User::findOrFail($id);
        return view('admin.frontend.users.create', compact('user'));
    }
    public function updateUser(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|unique:users,username,' . $user->id,
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'dob' => 'nullable|date',
            'phone' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'playerType' => 'nullable|in:Bowler,Batsman,Wicket-keeper,All-Rounder',
        ]);

        $user->update([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'dob' => $request->dob,
            'phone' => $request->phone,
            'address' => $request->address,
            'playerType' => $request->playerType,
        ]);

        return redirect()->route('admin.users')->with('success', 'User updated successfully.');
    }


    public function deleteUser($id): RedirectResponse
    {
        try {
            $user = User::findOrFail($id);
            $user->delete();
            return redirect()->route('admin.users')->with('success', 'User deleted successfully');
        } catch (QueryException $e) {
            return redirect()->route('admin.users')->with('error', 'Failed to delete user: ' . $e->getMessage());
        }
    }
}
