<?php

namespace App\Http\Controllers;

use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TeamController extends Controller
{

    public function index()
    {
        $teams = Team::all();
        return response()->json($teams);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|unique:teams',
            'user_id' => 'required|exists:users,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $team = Team::create($request->all());
        return response()->json($team, 201);
    }

    public function show($id)
    {
        $team = Team::find($id);
        if (!$team) {
            return response()->json(['error' => 'Team not found'], 404);
        }

        return response()->json($team);
    }

    public function update(Request $request, $id)
    {
        $team = Team::find($id);
        if (!$team) {
            return response()->json(['error' => 'Team not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|unique:teams,name,' . $team->id,
            'user_id' => 'required|exists:users,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $team->update($request->all());
        return response()->json($team, 200);
    }

    public function destroy($id)
    {
        $team = Team::find($id);
        if (!$team) {
            return response()->json(['error' => 'Team not found'], 404);
        }

        $team->delete();
        return response()->json(null, 204);
    }

}
