<?php

namespace App\Http\Controllers;

use App\Models\BowlingStats;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BowlingStatsController extends Controller
{
    public function index()
    {
        $bowlingStats = BowlingStats::all();
        return response()->json(['bowlingStats' => $bowlingStats]);
    }

    public function store(Request $request)
    {
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

        $bowlingStats = BowlingStats::create($validator->validated());
        return response()->json(['bowlingStats' => $bowlingStats], 201);
    }

    public function show($id)
    {
        $bowlingStats = BowlingStats::find($id);
        if (!$bowlingStats) {
            return response()->json(['error' => 'Bowling stats not found'], 404);
        }
        return response()->json(['bowlingStats' => $bowlingStats]);
    }

    public function update(Request $request, $id)
    {
        $bowlingStats = BowlingStats::find($id);
        if (!$bowlingStats) {
            return response()->json(['error' => 'Bowling stats not found'], 404);
        }

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

        $bowlingStats->update($validator->validated());
        return response()->json(['bowlingStats' => $bowlingStats], 200);
    }

    public function destroy($id)
    {
        $bowlingStats = BowlingStats::find($id);
        if (!$bowlingStats) {
            return response()->json(['error' => 'Bowling stats not found'], 404);
        }
        $bowlingStats->delete();
        return response()->json(['message' => 'Bowling stats deleted successfully']);
    }
}
