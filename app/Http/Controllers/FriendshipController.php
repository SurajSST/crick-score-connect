<?php

namespace App\Http\Controllers;

use App\Models\Friendship;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FriendshipController extends Controller
{
    public function index()
    {
        $friendships = Friendship::all();
        return response()->json($friendships, 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user1_id' => 'required|exists:users,id',
            'user2_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->first(), 400);
        }

        $friendship = Friendship::create($request->all());
        return response()->json($friendship, 201);
    }

    public function show($id)
    {
        $friendship = Friendship::findOrFail($id);
        return response()->json($friendship, 200);
    }

    public function destroy($id)
    {
        $friendship = Friendship::findOrFail($id);
        $friendship->delete();
        return response()->json('Friendship deleted successfully', 200);
    }
}
