<?php

namespace App\Http\Controllers;

use App\Models\BattingStats;
use App\Models\BowlingStats;
use App\Models\Matches;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class MatchController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'team1.name' => 'required|string',
            'team1.type' => 'required|in:home,away',
            'team1.users.*' => 'required|exists:users,id',
            'team2.name' => 'required|string',
            'team2.type' => 'required|in:home,away',
            'team2.users.*' => 'required|exists:users,id',
            'date' => 'required|date',
            'time' => 'required',
            'venue' => 'required|string',
            'overs' => 'required|numeric',
            'players_per_team' => 'required|numeric',
            'toss_winner' => 'required|in:team1,team2'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        DB::beginTransaction();

        try {
            // Create Team 1
            $team1 = new Team([
                'user_id' => $request->input('user_id'),
                'name' => $request->input('team1.name'),
                'type' => $request->input('team1.type'),
            ]);
            $team1->save();
            $team1->players()->attach($request->input('team1.users'));

            // Create Team 2
            $team2 = new Team([
                'user_id' => $request->input('user_id'),
                'name' => $request->input('team2.name'),
                'type' => $request->input('team2.type'),
            ]);
            $team2->save();
            $team2->players()->attach($request->input('team2.users'));

            // Determine the toss winner
            $tossWinnerId = $request->input('toss_winner') === 'team1' ? $team1->id : $team2->id;

            // Create the match
            $matchData = $request->only(['date', 'time', 'venue', 'overs', 'players_per_team']);
            $matchData['team1_id'] = $team1->id;
            $matchData['team2_id'] = $team2->id;
            $matchData['toss_winner_id'] = $tossWinnerId;

            $match = Matches::create($matchData);

            // Generate and assign a unique key
            $uniqueKey = '#' . $this->generateUniqueNumericKey() . $this->generateUniqueAlphaKey();
            $match->key = $uniqueKey;

            $match->save();

            DB::commit();

            return response()->json($match, 201);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    private function generateUniqueNumericKey()
    {
        return str_pad(mt_rand(1, 999), 3, '0', STR_PAD_LEFT);
    }

    private function generateUniqueAlphaKey()
    {
        return chr(rand(65, 90)); // Generates a random uppercase letter (A-Z)
    }


    public function sendGameResponse(Request $request, $matchId)
    {
        $match = Matches::with(['team1.users', 'team2.users'])->findOrFail($matchId);

        $homeTeam = $match->team1->users->map(function ($teamPlayer) {
            $user = $teamPlayer->user;
            return [
                'id' => $teamPlayer->id,
                'name' => $teamPlayer->name,
                'username' => $teamPlayer->username,
                'battingStats' => $teamPlayer->battingStats,
                'bowlingStats' => $teamPlayer->bowlingStats,
            ];
        });

        $awayTeam = $match->team2->users->map(function ($teamPlayer) {
            $user = $teamPlayer->user;
            return [
                'id' => $teamPlayer->id,
                'name' => $teamPlayer->name,
                'username' => $teamPlayer->username,
                'battingStats' => $teamPlayer->battingStats,
                'bowlingStats' => $teamPlayer->bowlingStats,
            ];
        });

        // Prepare the response
        $response = [
            "isGameFinished" => $match->isGameFinished,
            "finishedMessage" => $match->finishedMessage,
            "isGameCanceled" => $match->isGameCanceled,
            "user_id" => $match->user_id,
            "target" => $match->target,
            "CRR" => $match->CRR,
            "RRR" => $match->RRR,
            "extras" => $match->extras,
            "homeTeam" => $homeTeam,
            "awayTeam" => $awayTeam
        ];

        // Return the response as JSON
        return response()->json($response);
    }
}
