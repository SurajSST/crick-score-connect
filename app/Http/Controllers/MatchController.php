<?php

namespace App\Http\Controllers;

use App\Models\BattingStats;
use App\Models\BowlingStats;
use App\Models\Friendship;
use App\Models\Innings;
use App\Models\Matches;
use App\Models\MatchPayment;
use App\Models\Team;
use App\Models\TeamPlayer;
use App\Models\User;
use Exception;
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
            // Retrieve the user_id from the request
            $userId = $request->input('user_id');

            // Create Team 1
            $team1 = new Team([
                'user_id' => $userId,
                'name' => $request->input('team1.name'),
                'type' => $request->input('team1.type'),
            ]);
            $team1->save();

            // Associate users with Team 1
            foreach ($request->input('team1.users') as $userId) {
                $team1->users()->attach($userId);
            }

            // Create Team 2
            $team2 = new Team([
                'user_id' => $userId,
                'name' => $request->input('team2.name'),
                'type' => $request->input('team2.type'),
            ]);
            $team2->save();

            // Associate users with Team 2
            foreach ($request->input('team2.users') as $userId) {
                $team2->users()->attach($userId);
            }

            // Determine the toss winner
            $tossWinnerId = $request->input('toss_winner') === 'team1' ? $team1->id : $team2->id;

            // Create the match
            $matchData = $request->only(['date', 'time', 'venue', 'overs', 'players_per_team']);
            $matchData['team1_id'] = $team1->id;
            $matchData['team2_id'] = $team2->id;
            $matchData['toss_winner_id'] = $tossWinnerId;
            $matchData['user_id'] = $userId;
            $extras = [
                'byes' => 0,
                'legByes' => 0,
                'wide' => 0,
                'noBall' => 0,
                'penalty' => 0,
            ];
            $matchData['extras'] = $extras;
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
        return chr(rand(65, 90));
    }


    public function updateGameData(Request $request)
    {
        $data = $request->json()->all();

        $matchId = $data['match_id'];
        $match = Matches::findOrFail($matchId);

        $matchDetails = [
            'isGameFinished' => $data['isGameFinished'],
            'finishedMessage' => $data['finishedMessage'],
            'isGameCanceled' => $data['isGameCanceled'],
            'target' => $data['target'],
            'CRR' => $data['CRR'],
            'RRR' => $data['RRR'],
        ];

        $match->update($matchDetails);
        $match->update(['extras' => json_encode($data['extras'])]);

        $this->updateTeamStats($matchId, $data['homeTeam'], $data['team1_id'], true);
        $this->updateTeamStats($matchId, $data['awayTeam'], $data['team2_id'], false);

        if ($data['isFirstInning']) {
            $this->createInningsRecord($matchId, $data['team1_id'], $data['team2_id'], '1st innings');
        } else {
            $this->createInningsRecord($matchId, $data['team2_id'], $data['team1_id'], '2nd innings');
        }

        return response()->json(['message' => 'Game data updated successfully'], 200);
    }

    private function updateTeamStats($matchId, $teamData, $teamId, $isBattingTeam)
    {
        foreach ($teamData as $player) {
            $playerId = $player['id'];

            if ($isBattingTeam && $player['striker']) {
                $battingStats = $player['matchBattingStat'];
                $battingStats['innings_id'] = 1;
                BattingStats::updateOrCreate(['user_id' => $playerId, 'match_id' => $matchId], $battingStats);
            }

            if (!$isBattingTeam && $player['bowler']) {
                $bowlingStats = $player['matchBowlingStat'];
                $bowlingStats['innings_id'] = 1;
                BowlingStats::updateOrCreate(['user_id' => $playerId, 'match_id' => $matchId], $bowlingStats);
            }
        }
    }

    private function createInningsRecord($matchId, $battingTeamId, $bowlingTeamId, $inningsNumber)
    {
        // Check if the innings record already exists for the specified match and innings number
        $innings = Innings::where('match_id', $matchId)
            ->where('innings_number', $inningsNumber)
            ->first();

        if (!$innings) {
            // If innings record doesn't exist, create a new one
            Innings::create([
                'match_id' => $matchId,
                'batting_team_id' => $battingTeamId,
                'bowling_team_id' => $bowlingTeamId,
                'innings_number' => $inningsNumber,
            ]);
        }
    }


    public function sendGameResponse(Request $request)
    {

        $userId = $request->input('user_id');
        $key = $request->input('key');

        $matchId = Matches::where('key', $key)->value('id');

        if (!$matchId) {
            return response()->json(['error' => 'Invalid match key.'], 404);
        }
        $paymentExists = MatchPayment::where('match_id', $matchId)
            ->where('user_id', $userId)
            ->exists();

        if (!$paymentExists) {
            return response()->json(['error' => 'You have not paid for this match.'], 403);
        }
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
                'overs' => (float) $teamPlayer->bowlingStats->sum('overs'),
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
                'overs' => (float) $teamPlayer->bowlingStats->sum('overs'),
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
            "firstInningTotalOver" => (float) $firstInningTotalBalls / 6,
            "firstInningTotalWicket" => $firstInningTotalWickets,
            "secondInningTotalRun" => $secondInningTotalRuns,
            "secondInningTotalOver" => (float) $secondInningTotalBalls / 6,
            "secondInningTotalWicket" => $secondInningTotalWickets,

            "homeTeam" => $homeTeam,
            "awayTeam" => $awayTeam,
        ];

        return response()->json($response);
    }


    public function paymentStore(Request $request)
    {
        // Validating incoming request data
        $validatedData = $request->validate([
            'transaction_id' => 'required|string',
            'user_id' => 'required|exists:users,id',
            'key' => 'required|exists:matches,key',
        ]);

        $matchId = Matches::where('key', $validatedData['key'])->value('id');

        $validatedData['match_id'] = $matchId;

        MatchPayment::create($validatedData);

        return response()->json(['message' => 'Payment data stored successfully'], 200);
    }

    public function getUserSummary($userId)
    {
        $userExists = User::where('id', $userId)->exists();

        if (!$userExists) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $totalFriendships = Friendship::where('user1_id', $userId)
            ->orWhere('user2_id', $userId)
            ->count();

        // Count the total number of unique matches played by the user from batting_stats and bowling_stats tables
        $totalBattingMatches = BattingStats::where('user_id', $userId)->distinct()->pluck('match_id')->toArray();
        $totalBowlingMatches = BowlingStats::where('user_id', $userId)->distinct()->pluck('match_id')->toArray();
        $totalMatchesPlayed = count(array_unique(array_merge($totalBattingMatches, $totalBowlingMatches)));

        $summary = [
            'Total_friend' => $totalFriendships,
            'Total_match_played' => $totalMatchesPlayed
        ];

        return response()->json($summary, 200);
    }


    public function sendAllPaidMatchesData(Request $request)
    {
        $userId = $request->input('user_id');

        $paidMatchIds = MatchPayment::where('user_id', $userId)->pluck('match_id')->toArray();

        $matches = Matches::whereIn('id', $paidMatchIds)->get();

        $responseData = [];

        foreach ($matches as $match) {
            $responseData[] = [
                "match_id" => $match->id,
                "match_key" => $match->key,
                "team1Name" => $match->team1->name,
                "team2Name" => $match->team2->name
            ];
        }

        return response()->json($responseData);
    }
    public function sendAllUserMatchesData(Request $request)
    {
        $userId = $request->input('user_id');

        $matches = Matches::where('user_id', $userId)->get();

        $responseData = [];

        foreach ($matches as $match) {
            $responseData[] = [
                "match_id" => $match->id,
                "match_key" => $match->key,
                "team1Name" => $match->team1->name,
                "team2Name" => $match->team2->name
            ];
        }

        return response()->json($responseData);
    }

    public function sendResponse(Request $request)
    {

        $userId = $request->input('user_id');
        $key = $request->input('key');

        $matchId = Matches::where('key', $key)->value('id');

        if (!$matchId) {
            return response()->json(['error' => 'Invalid match key.'], 404);
        }
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
                'overs' => (float) $teamPlayer->bowlingStats->sum('overs'),
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
                'overs' => (float) $teamPlayer->bowlingStats->sum('overs'),
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
            "firstInningTotalOver" => (float) $firstInningTotalBalls / 6,
            "firstInningTotalWicket" => $firstInningTotalWickets,
            "secondInningTotalRun" => $secondInningTotalRuns,
            "secondInningTotalOver" => (float) $secondInningTotalBalls / 6,
            "secondInningTotalWicket" => $secondInningTotalWickets,

            "homeTeam" => $homeTeam,
            "awayTeam" => $awayTeam,
        ];

        return response()->json($response);
    }
}
