<?php

namespace App\Http\Controllers;

use App\Models\Matches;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MatchesController extends Controller
{
    public function index()
    {
        $matches = Matches::all();
        return response()->json($matches);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'team1_id' => 'required|exists:teams,id',
            'team2_id' => 'required|exists:teams,id|different:team1_id',
            'date' => 'required|date',
            'time' => 'required',
            'toss_winner_id' => 'required|exists:teams,id|different:team1_id|different:team2_id',
            'venue' => 'required|string',
            'overs' => 'required|numeric',
            'players_per_team' => 'required|numeric'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $match = Matches::create($request->all());
        return response()->json($match, 201);
    }

    public function show($id)
    {
        $match = Matches::find($id);
        if (!$match) {
            return response()->json(['error' => 'Match not found'], 404);
        }

        return response()->json($match);
    }

    public function update(Request $request, $id)
    {
        $match = Matches::find($id);
        if (!$match) {
            return response()->json(['error' => 'Match not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'team1_id' => 'required|exists:teams,id',
            'team2_id' => 'required|exists:teams,id|different:team1_id',
            'date' => 'required|date',
            'time' => 'required',
            'toss_winner_id' => 'required|exists:teams,id|different:team1_id|different:team2_id',
            'venue' => 'required|string',
            'overs' => 'required|numeric',
            'players_per_team' => 'required|numeric'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $match->update($request->all());
        return response()->json($match, 200);
    }

    public function destroy($id)
    {
        $match = Matches::find($id);
        if (!$match) {
            return response()->json(['error' => 'Match not found'], 404);
        }

        $match->delete();
        return response()->json(null, 204);
    }
}
