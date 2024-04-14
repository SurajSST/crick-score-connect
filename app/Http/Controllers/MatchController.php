<?php

namespace App\Http\Controllers;

use App\Models\BattingStats;
use App\Models\BowlingStats;
use App\Models\Matches;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MatchController extends Controller
{
    public function store(Request $request)
    {
        if ($request->filled(['team_id', 'user_id'])) {
            $validator = Validator::make($request->all(), [
                'team_id' => 'required|exists:teams,id',
                'user_id' => 'required|exists:users,id',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()], 400);
            }

            $team = Team::find($request->input('team_id'));
            if (!$team) {
                return response()->json(['error' => 'Team not found'], 404);
            }

            $user = User::find($request->input('user_id'));
            if (!$user) {
                return response()->json(['error' => 'User not found'], 404);
            }

            $team->users()->attach($user);

            return response()->json(['message' => 'Player added to team successfully']);
        }

        if ($request->filled(['team1_id', 'team2_id', 'date', 'time', 'toss_winner_id', 'venue', 'overs', 'players_per_team'])) {
            $validator = Validator::make($request->all(), [
                'team1_id' => 'required|exists:teams,id',
                'team2_id' => 'required|exists:teams,id|different:team1_id',
                'date' => 'required|date',
                'time' => 'required',
                'toss_winner_id' => 'required|exists:teams,id|different:team1_id|different:team2_id',
                'venue' => 'required|string',
                'overs' => 'required|numeric',
                'players_per_team' => 'required|numeric',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()], 400);
            }

            $match = Matches::create($request->all());
            return response()->json(['message' => 'Match created successfully'], 201);
        }

        if ($request->filled(['user_id', 'match_id', 'innings_id', 'runs_scored', 'fours', 'sixes', 'strike_rate', 'balls_faced'])) {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|exists:users,id',
                'match_id' => 'required|exists:matches,id',
                'innings_id' => 'required|exists:innings,id',
                'runs_scored' => 'required|integer',
                'fours' => 'required|integer',
                'sixes' => 'required|integer',
                'strike_rate' => 'required|numeric',
                'balls_faced' => 'required|integer',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()], 400);
            }

            $battingStats = BattingStats::create($request->all());
            return response()->json(['message' => 'Batting stats created successfully'], 201);
        }

        if ($request->filled(['user_id', 'match_id', 'innings_id', 'overs_bowled', 'runs_conceded', 'wickets_taken', 'maidens', 'economy_rate'])) {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|exists:users,id',
                'match_id' => 'required|exists:matches,id',
                'innings_id' => 'required|exists:innings,id',
                'overs_bowled' => 'required|numeric',
                'runs_conceded' => 'required|integer',
                'wickets_taken' => 'required|integer',
                'maidens' => 'required|integer',
                'economy_rate' => 'required|numeric',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()], 400);
            }

            $bowlingStats = BowlingStats::create($request->all());
            return response()->json(['message' => 'Bowling stats created successfully'], 201);
        }

        return response()->json(['error' => 'Invalid request'], 400);
    }
}
