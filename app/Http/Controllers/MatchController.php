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
use Illuminate\Support\Arr;
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
            'RRR' => Arr::get($data, 'RRR', 0),
            'first_inning_total_run' => $data['firstInningTotalRun'],
            'first_inning_total_over' => $data['firstInningTotalOver'],
            'first_inning_total_wicket' => $data['firstInningTotalWicket'],
            'second_inning_total_run' => $data['secondInningTotalRun'],
            'second_inning_total_over' => $data['secondInningTotalOver'],
            'second_inning_total_wicket' => $data['second_inning_total_wicket'],
        ];

        $match->update($matchDetails);
        $jsonData = json_encode($data['extras'], JSON_UNESCAPED_SLASHES);
        $decodedData = json_decode($jsonData, true); // Decode to associative array
        $match->update(['extras' => $decodedData]);  // Update with decoded data

        if ($data['isFirstInning']) {
            $this->createInningsRecord($matchId, $data['team1_id'], $data['team2_id'], '1st innings');
        } else {
            $this->createInningsRecord($matchId, $data['team2_id'], $data['team1_id'], '2nd innings');
        }

        $this->updateTeamStats($matchId, $data['homeTeam'], $data['team1_id'], true);
        $this->updateTeamStats($matchId, $data['awayTeam'], $data['team2_id'], false);

        return response()->json(['message' => 'Game data updated successfully'], 200);
    }

    private function updateTeamStats($matchId, $teamData, $teamId, $isBattingTeam)
    {
        $inningsNumber = $isBattingTeam ? '1st innings' : '2nd innings';

        $innings = Innings::where('match_id', $matchId)
            ->where('innings_number', $inningsNumber)
            ->first();

        $inningsId = $innings ? $innings->id : 1;

        foreach ($teamData as $player) {
            $playerId = $player['id'];

            $battingStats = [
                'runs_scored' => $player['matchBattingStat']['runs'],
                'fours' => $player['matchBattingStat']['fours'] ?? 0,
                'sixes' => $player['matchBattingStat']['sixes'] ?? 0,
                'balls_faced' => $player['matchBattingStat']['balls'] ?? 0,
                'is_striker' => $player['striker'],
                'is_non_striker' => $player['nonStriker'],
                'out' => $player['out'],
                'innings_id' => $inningsId,
            ];

            $strikeRate = ($battingStats['balls_faced'] > 0) ?
                ($battingStats['runs_scored'] / $battingStats['balls_faced']) * 100 : 0;
            $battingStats['strike_rate'] = round($strikeRate, 2);

            // Update or create batting stats
            BattingStats::updateOrCreate(
                ['user_id' => $playerId, 'match_id' => $matchId, 'innings_id' => $inningsId],
                $battingStats
            );

            $bowlingStats = [
                'balls' => $player['matchBowlingStat']['balls'] ?? 0,
                'wides' => $player['matchBowlingStat']['wides'] ?? 0,
                'noBalls' => $player['matchBowlingStat']['noBalls'] ?? 0,
                'overs_bowled' => $player['matchBowlingStat']['overs'] ?? 0,
                'runs_conceded' => $player['matchBowlingStat']['runs'] ?? 0,
                'wickets_taken' => $player['matchBowlingStat']['wickets'] ?? 0,
                'maidens' => $player['matchBowlingStat']['maidens'] ?? 0,
                'is_bowling' => $player['bowler'],
                'innings_id' => $inningsId,
            ];

            // Calculate economy rate if overs bowled is not 0
            $economyRate = ($bowlingStats['overs_bowled'] > 0) ?
                $bowlingStats['runs_conceded'] / $bowlingStats['overs_bowled'] : 0;
            $bowlingStats['economy_rate'] = round($economyRate, 2);

            BowlingStats::updateOrCreate(
                ['user_id' => $playerId, 'match_id' => $matchId, 'innings_id' => $inningsId],
                $bowlingStats
            );
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
        $firstInningTotalRuns = $match->first_inning_total_run ?? 0;
        $firstInningTotalBalls = $match->first_inning_total_over ?? 0; // Assuming the column name is 'first_inning_total_over'
        $firstInningTotalWickets = $match->first_inning_total_wicket ?? 0;

        $secondInningTotalRuns = $match->second_inning_total_run ?? 0;
        $secondInningTotalBalls = $match->second_inning_total_over ?? 0; // Assuming the column name is 'second_inning_total_over'
        $secondInningTotalWickets = $match->second_inning_total_wicket ?? 0;

        $homeTeam = $match->team1->users->map(function ($teamPlayer) use ($matchId) {
            $battingStats = BattingStats::where('match_id', $matchId)
                ->where('user_id', $teamPlayer->id)
                ->first();

            $bowlingStats = BowlingStats::where('match_id', $matchId)
                ->where('user_id', $teamPlayer->id)
                ->first();

            return [
                'id' => $teamPlayer->id,
                'name' => $teamPlayer->name,
                'username' => $teamPlayer->username,
                'striker' => $battingStats ? (bool) $battingStats->is_striker : false,
                'nonStriker' => $battingStats ? (bool) $battingStats->is_non_striker : false,
                'bowler' => $bowlingStats ? (bool) $bowlingStats->is_bowling : false,
                'out' => $battingStats ? (bool) $battingStats->out : false,
                'matchBattingStat' => [
                    'runs' => $battingStats ? (int) $battingStats->runs_scored : 0,
                    'balls' => $battingStats ? (int) $battingStats->balls_faced : 0,
                    'fours' => $battingStats ? (int) $battingStats->fours : 0,
                    'sixes' => $battingStats ? (int) $battingStats->sixes : 0,
                ],
                'matchBowlingStat' => [
                    'runs' => $bowlingStats ? (int) $bowlingStats->runs : 0,
                    'balls' => $bowlingStats ? (int) $bowlingStats->balls : 0,
                    'fours' => $bowlingStats ? (int) $bowlingStats->fours : 0,
                    'sixes' => $bowlingStats ? (int) $bowlingStats->sixes : 0,
                    'wides' => $bowlingStats ? (int) $bowlingStats->wides : 0,
                    'noBalls' => $bowlingStats ? (int) $bowlingStats->noBalls : 0,
                    'maidens' => $bowlingStats ? (int) $bowlingStats->maidens : 0,
                    'wickets' => $bowlingStats ? (int) $bowlingStats->wickets : 0,
                    'overs' => $bowlingStats ? (float) $bowlingStats->overs : 0,
                ],
            ];
        });

        $awayTeam = $match->team2->users->map(function ($teamPlayer) use ($matchId) {
            $battingStats = BattingStats::where('match_id', $matchId)
                ->where('user_id', $teamPlayer->id)
                ->first();

            $bowlingStats = BowlingStats::where('match_id', $matchId)
                ->where('user_id', $teamPlayer->id)
                ->first();

            return [
                'id' => $teamPlayer->id,
                'name' => $teamPlayer->name,
                'username' => $teamPlayer->username,
                'striker' => $battingStats ? (bool) $battingStats->is_striker : false,
                'nonStriker' => $battingStats ? (bool) $battingStats->is_non_striker : false,
                'bowler' => $bowlingStats ? (bool) $bowlingStats->is_bowling : false,
                'out' => $battingStats ? (bool) $battingStats->out : false,
                'matchBattingStat' => [
                    'runs' => $battingStats ? (int)$battingStats->runs_scored : 0,
                    'balls' => $battingStats ? (int)$battingStats->balls_faced : 0,
                    'fours' => $battingStats ? (int)$battingStats->fours : 0,
                    'sixes' => $battingStats ? (int)$battingStats->sixes : 0,
                ],

                'matchBowlingStat' => [
                    'runs' => $bowlingStats ? (int)$bowlingStats->runs_conceded : 0,
                    'balls' => $bowlingStats ? (int)$bowlingStats->balls : 0,
                    'fours' => $bowlingStats ? (int)$bowlingStats->fours : 0,
                    'sixes' => $bowlingStats ? (int)$bowlingStats->sixes : 0,
                    'wides' => $bowlingStats ? (int)$bowlingStats->wides : 0,
                    'noBalls' => $bowlingStats ? (int)$bowlingStats->noBalls : 0,
                    'maidens' => $bowlingStats ? (int)$bowlingStats->maidens : 0,
                    'wickets' => $bowlingStats ? (int)$bowlingStats->wickets_taken : 0,
                    'overs' => $bowlingStats ? (float)$bowlingStats->overs_bowled : 0.0,
                ],

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
        $firstInningTotalRuns = $firstInning ? $firstInning->battingStats->sum('runs_scored') : 0;
        $firstInningTotalBalls = $firstInning ? $firstInning->bowlingStats->sum('balls') : 0;
        $firstInningTotalWickets = $firstInning ? $firstInning->bowlingStats->sum('wickets') : 0;

        $secondInningTotalRuns = $secondInning ? $secondInning->battingStats->sum('runs_scored') : 0;
        $secondInningTotalBalls = $secondInning ? $secondInning->bowlingStats->sum('balls') : 0;
        $secondInningTotalWickets = $secondInning ? $secondInning->bowlingStats->sum('wickets') : 0;

        $homeTeam = $match->team1->users->map(function ($teamPlayer) use ($matchId) {
            $battingStats = BattingStats::where('match_id', $matchId)
                ->where('user_id', $teamPlayer->id)
                ->first();

            $bowlingStats = BowlingStats::where('match_id', $matchId)
                ->where('user_id', $teamPlayer->id)
                ->first();

            return [
                'id' => $teamPlayer->id,
                'name' => $teamPlayer->name,
                'username' => $teamPlayer->username,
                'striker' => $battingStats ? (bool) $battingStats->is_striker : false,
                'nonStriker' => $battingStats ? (bool) $battingStats->is_non_striker : false,
                'bowler' => $bowlingStats ? (bool) $bowlingStats->is_bowling : false,
                'out' => $battingStats ? (bool) $battingStats->out : false,
                'matchBattingStat' => [
                    'runs' => $battingStats ? $battingStats->runs_scored : 0,
                    'balls' => $battingStats ? $battingStats->balls_faced : 0,
                    'fours' => $battingStats ? $battingStats->fours : 0,
                    'sixes' => $battingStats ? $battingStats->sixes : 0,
                ],
                'matchBowlingStat' => [
                    'runs' => $bowlingStats ? $bowlingStats->runs : 0,
                    'balls' => $bowlingStats ? $bowlingStats->balls : 0,
                    'fours' => $bowlingStats ? $bowlingStats->fours : 0,
                    'sixes' => $bowlingStats ? $bowlingStats->sixes : 0,
                    'wides' => $bowlingStats ? $bowlingStats->wides : 0,
                    'noBalls' => $bowlingStats ? $bowlingStats->noBalls : 0,
                    'maidens' => $bowlingStats ? $bowlingStats->maidens : 0,
                    'wickets' => $bowlingStats ? $bowlingStats->wickets : 0,
                    'overs' => $bowlingStats ? (float) $bowlingStats->overs : 0,
                ],
            ];
        });

        $awayTeam = $match->team2->users->map(function ($teamPlayer) use ($matchId) {
            $battingStats = BattingStats::where('match_id', $matchId)
                ->where('user_id', $teamPlayer->id)
                ->first();

            $bowlingStats = BowlingStats::where('match_id', $matchId)
                ->where('user_id', $teamPlayer->id)
                ->first();

            return [
                'id' => $teamPlayer->id,
                'name' => $teamPlayer->name,
                'username' => $teamPlayer->username,
                'striker' => $battingStats ? (bool) $battingStats->is_striker : false,
                'nonStriker' => $battingStats ? (bool) $battingStats->is_non_striker : false,
                'bowler' => $bowlingStats ? (bool) $bowlingStats->is_bowling : false,
                'out' => $battingStats ? (bool) $battingStats->out : false,
                'matchBattingStat' => [
                    'runs' => $battingStats ? $battingStats->runs_scored : 0,
                    'balls' => $battingStats ? $battingStats->balls_faced : 0,
                    'fours' => $battingStats ? $battingStats->fours : 0,
                    'sixes' => $battingStats ? $battingStats->sixes : 0,
                ],
                'matchBowlingStat' => [
                    'runs' => $bowlingStats ? $bowlingStats->runs : 0,
                    'balls' => $bowlingStats ? $bowlingStats->balls : 0,
                    'fours' => $bowlingStats ? $bowlingStats->fours : 0,
                    'sixes' => $bowlingStats ? $bowlingStats->sixes : 0,
                    'wides' => $bowlingStats ? $bowlingStats->wides : 0,
                    'noBalls' => $bowlingStats ? $bowlingStats->noBalls : 0,
                    'maidens' => $bowlingStats ? $bowlingStats->maidens : 0,
                    'wickets' => $bowlingStats ? $bowlingStats->wickets : 0,
                    'overs' => $bowlingStats ? (float) $bowlingStats->overs : 0,
                ],
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
