<?php

namespace App\Http\Controllers;

use App\Models\Innings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class InningsController extends Controller
{
    public function index()
    {
        $innings = Innings::all();
        return response()->json(['innings' => $innings]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'match_id' => 'required|exists:matches,id',
            'batting_team_id' => 'required|exists:teams,id',
            'bowling_team_id' => 'required|exists:teams,id',
            'innings_number' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $innings = Innings::create($validator->validated());
        return response()->json(['innings' => $innings], 201);
    }

    public function show($id)
    {
        $innings = Innings::find($id);
        if (!$innings) {
            return response()->json(['error' => 'Innings not found'], 404);
        }
        return response()->json(['innings' => $innings]);
    }

    public function update(Request $request, $id)
    {
        $innings = Innings::find($id);
        if (!$innings) {
            return response()->json(['error' => 'Innings not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'match_id' => 'required|exists:matches,id',
            'batting_team_id' => 'required|exists:teams,id',
            'bowling_team_id' => 'required|exists:teams,id',
            'innings_number' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $innings->update($validator->validated());
        return response()->json(['innings' => $innings], 200);
    }

    public function destroy($id)
    {
        $innings = Innings::find($id);
        if (!$innings) {
            return response()->json(['error' => 'Innings not found'], 404);
        }
        $innings->delete();
        return response()->json(['message' => 'Innings deleted successfully']);
    }
}
