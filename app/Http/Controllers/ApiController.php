<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;

class ApiController extends Controller
{
    public function searchUsers(Request $request)
    {
        try {
            $query = $request->input('query');
            $users = User::where('name', 'LIKE', "%$query%")
                ->orWhere('email', 'LIKE', "%$query%")
                ->orWhere('username', 'LIKE', "%$query%")
                ->get(['id', 'name', 'username']);

            return response()->json($users, 200);
        } catch (QueryException $e) {
            return response()->json(['error' => 'Failed to search users. Please try again later.'], 500);
        }
    }
}
