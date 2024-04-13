<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;

class ApiController extends Controller
{
    public function searchUsers(Request $request)
    {
        try {
            $query = $request->input('query');

            if (!$query) {
                return response()->json([], 200);
            }

            $users = User::where('name', 'LIKE', "%$query%")
                ->orWhere('email', 'LIKE', "%$query%")
                ->orWhere('username', 'LIKE', "%$query%")
                ->get(['id', 'name', 'username']);

            return response()->json($users, 200);
        } catch (QueryException $e) {
            return response()->json(['error' => 'Failed to search users. Please try again later.'], 500);
        }
    }

    public function getUserStats($userId)
    {
        try {
            $user = User::findOrFail($userId);

            // Batting Stats
            $battingStats = $user->battingStats()->latest()->get();
            $totalRuns = $battingStats->sum('runs_scored');
            $totalBallsFaced = $battingStats->sum('balls_faced');
            $totalMatches = $battingStats->count();
            $totalInnings = $battingStats->whereNotNull('runs_scored')->count();
            $average = $totalInnings > 0 ? $totalRuns / $totalInnings : 0;
            $highestScore = $battingStats->max('runs_scored');
            $totalFours = $battingStats->sum('fours');
            $totalSixes = $battingStats->sum('sixes');
            $fifties = $battingStats->whereBetween('runs_scored', [50, 99])->count();
            $hundreds = $battingStats->where('runs_scored', '>=', 100)->count();
            $strikeRate = $totalBallsFaced > 0 ? ($totalRuns / $totalBallsFaced) * 100 : 0;

            // Bowling Stats
            $bowlingStats = $user->bowlingStats()->latest()->get();
            $totalWickets = $bowlingStats->sum('wickets_taken');
            $totalOvers = $bowlingStats->sum('overs_bowled');
            $totalRunsConceded = $bowlingStats->sum('runs_conceded');
            $totalMatchesBowled = $bowlingStats->count();
            $bowlingEconomyRate = $totalOvers > 0 ? $totalRunsConceded / $totalOvers : 0;
            $totalMaidens = $bowlingStats->sum('maidens');
            $bestBowling = $bowlingStats->groupBy('match_id')
                ->reduce(function ($bestSoFar, $matchStats) {
                    $maxWickets = $matchStats->max('wickets_taken');
                    $minRuns = $matchStats->min('runs_conceded');
                    if (!$bestSoFar || $maxWickets > $bestSoFar['wickets'] || ($maxWickets === $bestSoFar['wickets'] && $minRuns < $bestSoFar['runs'])) {
                        return ['wickets' => $maxWickets, 'runs' => $minRuns];
                    }
                    return $bestSoFar;
                }, []);

            return response()->json([
                'batting' => [
                    'matches' => $totalMatches,
                    'innings' => $totalInnings,
                    'runs' => $totalRuns,
                    'average' => round($average, 2),
                    'highest' => $highestScore,
                    'strikeRate' => round($strikeRate, 2),
                    'fours' => $totalFours,
                    'sixes' => $totalSixes,
                    'fifties' => $fifties,
                    'hundreds' => $hundreds,
                ],
                'bowling' => [
                    'matches' => $totalMatchesBowled,
                    'innings' => $totalMatchesBowled, // Assuming 1 innings per match
                    'runs' => $totalRunsConceded,
                    'overs' => $totalOvers,
                    'strikeRate' => 0, // Calculate strike rate if needed
                    'maidens' => $totalMaidens,
                    'wickets' => $totalWickets,
                    'bBowling' => $bestBowling,
                    'ecoRate' => round($bowlingEconomyRate, 2),
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'User stats not found'], 404);
        }
    }
}
