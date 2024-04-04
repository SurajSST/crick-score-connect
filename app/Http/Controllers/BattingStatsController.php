<?php

namespace App\Http\Controllers;

use App\Models\BattingStats;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BattingStatsController extends Controller
{
    public function index()
    {
        $battingStats = BattingStats::all();
        return response()->json(['battingStats' => $battingStats]);
    }

    public function store(Request $request)
    {
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

        $battingStats = BattingStats::create($validator->validated());
        return response()->json(['battingStats' => $battingStats], 201);
    }

    public function show($id)
    {
        $battingStats = BattingStats::find($id);
        if (!$battingStats) {
            return response()->json(['error' => 'Batting stats not found'], 404);
        }
        return response()->json(['battingStats' => $battingStats]);
    }

    public function update(Request $request, $id)
    {
        $battingStats = BattingStats::find($id);
        if (!$battingStats) {
            return response()->json(['error' => 'Batting stats not found'], 404);
        }

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

        $battingStats->update($validator->validated());
        return response()->json(['battingStats' => $battingStats], 200);
    }

    public function destroy($id)
    {
        $battingStats = BattingStats::find($id);
        if (!$battingStats) {
            return response()->json(['error' => 'Batting stats not found'], 404);
        }
        $battingStats->delete();
        return response()->json(['message' => 'Batting stats deleted successfully']);
    }
}
