<?php

namespace App\Http\Controllers;

use App\Models\BattingStats;
use App\Models\BowlingStats;
use App\Models\Innings;
use App\Models\Matches;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Ramsey\Uuid\Type\Decimal;

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
        $match = Matches::with(['team1.users.battingStats', 'team2.users.battingStats', 'team1.users.bowlingStats', 'team2.users.bowlingStats'])->findOrFail($matchId);
        $firstInning = Innings::where('match_id', $matchId)->where('innings_number', 1)->first();
        $secondInning = Innings::where('match_id', $matchId)->where('innings_number', 2)->first();
        $inningsCount = Innings::where('match_id', $matchId)->count();
        // Calculate total runs, overs, and wickets for the first inning
        $firstInningTotalRuns = $firstInning ? $firstInning->battingStats->sum('runs_scored') : 0;
        $firstInningTotalBalls = $firstInning ? $firstInning->bowlingStats->sum('balls') : 0;
        $firstInningTotalWickets = $firstInning ? $firstInning->bowlingStats->sum('wickets') : 0;

        // Calculate total runs, overs, and wickets for the second inning
        $secondInningTotalRuns = $secondInning ? $secondInning->battingStats->sum('runs_scored') : 0;
        $secondInningTotalBalls = $secondInning ? $secondInning->bowlingStats->sum('balls') : 0;
        $secondInningTotalWickets = $secondInning ? $secondInning->bowlingStats->sum('wickets') : 0;

        $homeTeam = $match->team1->users->map(function ($teamPlayer) {
            $battingStats = $teamPlayer->battingStats->isEmpty() ? null : [
                'runs' => $teamPlayer->battingStats->sum('runs_scored'),
                'balls' => $teamPlayer->battingStats->sum('balls_faced'),
                'fours' => $teamPlayer->battingStats->sum('fours'),
                'sixes' => $teamPlayer->battingStats->sum('sixes'),
            ];

            $bowlingStats = $teamPlayer->bowlingStats->isEmpty() ? null : [
                'runs' => $teamPlayer->bowlingStats->sum('runs'),
                'balls' => $teamPlayer->bowlingStats->sum('balls'),
                'fours' => $teamPlayer->bowlingStats->sum('fours'),
                'sixes' => $teamPlayer->bowlingStats->sum('sixes'),
                'wides' => $teamPlayer->bowlingStats->sum('wides'),
                'noBalls' => $teamPlayer->bowlingStats->sum('noBalls'),
                'maidens' => $teamPlayer->bowlingStats->sum('maidens'),
                'wickets' => $teamPlayer->bowlingStats->sum('wickets'),
                'overs' => (double) $teamPlayer->bowlingStats->sum('overs'),
            ];

            return [
                'id' => $teamPlayer->id,
                'name' => $teamPlayer->name,
                'username' => $teamPlayer->username,
                'striker' => (bool) $teamPlayer->battingStats->first()->is_striker,
                'nonStriker' => (bool) $teamPlayer->battingStats->first()->is_non_striker,
                'bowler' => (bool) $teamPlayer->bowlingStats->first()->is_bowling,
                'out' => (bool) $teamPlayer->battingStats->first()->out,
                'matchBattingStat' => $battingStats,
                'matchBowlingStat' => $bowlingStats,
            ];
        });

        $awayTeam = $match->team2->users->map(function ($teamPlayer) {
            $battingStats = $teamPlayer->battingStats->isEmpty() ? null : [
                'runs' => $teamPlayer->battingStats->sum('runs_scored'),
                'balls' => $teamPlayer->battingStats->sum('balls_faced'),
                'fours' => $teamPlayer->battingStats->sum('fours'),
                'sixes' => $teamPlayer->battingStats->sum('sixes'),
            ];

            $bowlingStats = $teamPlayer->bowlingStats->isEmpty() ? null : [
                'runs' => $teamPlayer->bowlingStats->sum('runs'),
                'balls' => $teamPlayer->bowlingStats->sum('balls'),
                'fours' => $teamPlayer->bowlingStats->sum('fours'),
                'sixes' => $teamPlayer->bowlingStats->sum('sixes'),
                'wides' => $teamPlayer->bowlingStats->sum('wides'),
                'noBalls' => $teamPlayer->bowlingStats->sum('noBalls'),
                'maidens' => $teamPlayer->bowlingStats->sum('maidens'),
                'wickets' => $teamPlayer->bowlingStats->sum('wickets'),
                'overs' => (double) $teamPlayer->bowlingStats->sum('overs'),
            ];

            return [
                'id' => $teamPlayer->id,
                'name' => $teamPlayer->name,
                'username' => $teamPlayer->username,
                'striker' => (bool) $teamPlayer->battingStats->first()->is_striker,
                'nonStriker' => (bool) $teamPlayer->battingStats->first()->is_non_striker,
                'bowler' => (bool) $teamPlayer->bowlingStats->first()->is_bowling,
                'out' => (bool) $teamPlayer->battingStats->first()->out,
                'matchBattingStat' => $battingStats,
                'matchBowlingStat' => $bowlingStats,
            ];
        });

        $response = [
            "isGameFinished" => (bool) $match->isGameFinished,
            "finishedMessage" => $match->finishedMessage ?? 'Default message',
            "isGameCanceled" => (bool) $match->isGameCanceled,
            "user_id" => $match->user_id,
            "target" => $match->target,
            "CRR" => $match->CRR,
            "RRR" => $match->RRR,
            "extras" => $match->extras,

            "homeTeamName" => $match->team1->name,
            "awayTeamName" => $match->team2->name,
            "isFirstInning" => $inningsCount == 1 ? true : false,
            "firstInningTotalRun" => $firstInningTotalRuns,
            "firstInningTotalOver" => (double) $firstInningTotalBalls / 6,
            "firstInningTotalWicket" => $firstInningTotalWickets,
            "secondInningTotalRun" => $secondInningTotalRuns,
            "secondInningTotalOver" => (double) $secondInningTotalBalls / 6,
            "secondInningTotalWicket" => $secondInningTotalWickets,

            "homeTeam" => $homeTeam,
            "awayTeam" => $awayTeam,
        ];

        return response()->json($response);
    }
}
