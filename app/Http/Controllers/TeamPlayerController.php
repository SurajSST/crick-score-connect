<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

class TeamPlayerController extends Controller
{
    public function store(Request $request, $teamId)
    {
        $team = Team::find($teamId);
        if (!$team) {
            return response()->json(['error' => 'Team not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'users.*' => 'required|exists:users,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        foreach ($request->input('users') as $userId) {
            $user = User::find($userId);
            if (!$user) {
                return response()->json(['error' => 'User not found'], 404);
            }
            $team->users()->attach($userId);
        }

        return response()->json(['message' => 'Players added to team successfully']);
    }


    public function destroy($teamId, $userId)
    {
        $team = Team::find($teamId);
        if (!$team) {
            return response()->json(['error' => 'Team not found'], 404);
        }

        $user = User::find($userId);
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $validator = Validator::make(['team_id' => $teamId, 'user_id' => $userId], [
            'team_id' => 'required|exists:teams,id',
            'user_id' => 'required|exists:users,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $team->users()->detach($user);

        return response()->json(['message' => 'Player removed from team successfully']);
    }
}
