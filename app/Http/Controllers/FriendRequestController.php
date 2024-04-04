<?php

namespace App\Http\Controllers;

use App\Models\FriendRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FriendRequestController extends Controller
{
    public function index()
    {
        $friendRequests = FriendRequest::all();
        return response()->json($friendRequests, 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sender_id' => 'required|exists:users,id',
            'receiver_id' => 'required|exists:users,id',
            'status' => 'required|in:pending,accepted,rejected',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->first(), 400);
        }

        $friendRequest = FriendRequest::create($request->all());
        return response()->json($friendRequest, 201);
    }

    public function show($id)
    {
        $friendRequest = FriendRequest::findOrFail($id);
        return response()->json($friendRequest, 200);
    }

    public function update(Request $request, $id)
    {
        $friendRequest = FriendRequest::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,accepted,rejected',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->first(), 400);
        }

        $friendRequest->update($request->all());
        return response()->json($friendRequest, 200);
    }

    public function destroy($id)
    {
        $friendRequest = FriendRequest::findOrFail($id);
        $friendRequest->delete();
        return response()->json('Friend request deleted successfully', 200);
    }
}
