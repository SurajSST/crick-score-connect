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

        // Update extras
        $match->update(['extras' => json_encode($data['extras'])]);

        // Update batting and bowling stats for both teams
        $this->updateTeamStats($matchId, $data['homeTeam'], $data['isFirstInning']);
        $this->updateTeamStats($matchId, $data['awayTeam'], !$data['isFirstInning']);

        // Create innings record if isFirstInning is true
        if ($data['isFirstInning']) {
            $this->createInningsRecord($matchId, $data['homeTeam'], '1st');
        } else {
            // Create innings record for 2nd innings
            $this->createInningsRecord($matchId, $data['awayTeam'], '2nd');
        }

        // Return success message
        return response()->json(['message' => 'Game data updated successfully'], 200);
    }

    private function updateTeamStats($matchId, $teamData, $isFirstInning)
    {
        foreach ($teamData as $player) {
            if ($player['striker'] || $player['bowler']) { // Check if the player is a striker or bowler
                $playerId = $player['id'];

                // Update batting stats if striker
                if ($player['striker']) {
                    $battingStats = $player['matchBattingStat'];
                    $battingStats['innings_id'] = $isFirstInning ? 1 : 2;
                    BattingStats::updateOrCreate(
                        ['user_id' => $playerId, 'match_id' => $matchId],
                        $battingStats
                    );
                }

                // Update bowling stats if bowler
                if ($player['bowler']) {
                    $bowlingStats = $player['matchBowlingStat'];
                    $bowlingStats['innings_id'] = $isFirstInning ? 1 : 2;
                    BowlingStats::updateOrCreate(
                        ['user_id' => $playerId, 'match_id' => $matchId],
                        $bowlingStats
                    );
                }
            }
        }
    }

    private function createInningsRecord($matchId, $teamData, $inningsNumber)
    {
        if (!empty($teamData)) {
            // Initialize variables to store batting and bowling team IDs
            $battingTeamId = null;
            $bowlingTeamId = null;

            // Get the match
            $match = Matches::findOrFail($matchId);

            // Debug: Check if the match is fetched correctly
            dd($match);

            // Loop through the team data to find the batting and bowling teams
            foreach ($teamData as $player) {
                // Get the user ID of the player
                $userId = $player['id'];

                // Debug: Print the player ID for debugging
                echo "Player ID: $userId\n";

                // Find the team ID to which the player belongs in the match
                $team = $match->teams()->whereHas('players', function ($query) use ($userId) {
                    $query->where('user_id', $userId);
                })->first();

                // Debug: Print the team found for debugging
                dd($team);

                if ($team) {
                    // Check if the player is a striker or bowler
                    if ($player['striker']) {
                        // The player is a striker, so their team ID is the batting team ID
                        $battingTeamId = $team->id;
                    }
                    if ($player['bowler']) {
                        // The player is a bowler, so their team ID is the bowling team ID
                        $bowlingTeamId = $team->id;
                    }
                }
            }

            // Debug: Print the retrieved team IDs for debugging
            echo "Batting Team ID: $battingTeamId\n";
            echo "Bowling Team ID: $bowlingTeamId\n";

            // Create the innings record if both batting and bowling team IDs are found
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
}
