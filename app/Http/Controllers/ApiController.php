<?php

namespace App\Http\Controllers;

use App\Models\FriendRequest;
use App\Models\Friendship;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;


class ApiController extends Controller
{
    public function userEdit(Request $request, $id)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'dob' => 'nullable|date',
                'phone' => 'nullable|string|max:255',
                'address' => 'nullable|string|max:255',
                'player_type' => 'nullable|in:Bowler,Batsman,Wicket-keeper,All-Rounder',
                'profile_photo_path' => 'nullable', // Max file size 10MB
            ]);

            $user = User::findOrFail($id);

            $user->name = $request->name;
            $user->dob = $request->dob;
            $user->phone = $request->phone;
            $user->address = $request->address;
            $user->playerType = $request->player_type;

            if ($request->hasFile('profile_photo_path')) {
                if ($user->profile_photo_path) {
                    Storage::delete($user->profile_photo_path);
                }
                $user->profile_photo_path = $request->file('profile_photo_path')->store('profile-photos', 'public');
            }

            $user->save();

            return response()->json([
                'message' => 'User updated successfully',
                'user' => $user
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'User not found.'], 404);
        } catch (ValidationException $e) {
            return response()->json(['error' => $e->validator->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => $e], 500);
        }
    }

    public function searchUsers(Request $request)
    {
        try {
            $query = $request->input('query');
            $authenticatedUserId = $request->input('user_id');

            // Validate the authenticated user ID
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|exists:users,id',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()], 400);
            }

            if (!$query) {
                return response()->json([], 200);
            }
            $users = User::where('name', 'LIKE', "%$query%")
                ->orWhere('email', 'LIKE', "%$query%")
                ->orWhere('username', 'LIKE', "%$query%")
                ->whereNotExists(function ($query) use ($authenticatedUserId) {
                    $query->select(DB::raw(1))
                        ->from('friend_requests')
                        ->where('status', 'pending')
                        ->where(function ($query) use ($authenticatedUserId) {
                            $query->where('sender_id', $authenticatedUserId)
                                ->orWhere('receiver_id', $authenticatedUserId);
                        })
                        ->whereRaw('sender_id = users.id OR receiver_id = users.id');
                })
                ->get(['id', 'name', 'username', 'profile_photo_path']);
            $appUrl = config('app.url');

            $users = $users->map(function ($user) use ($appUrl) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'username' => $user->username,
                    'profile_photo_url' => $appUrl . '/storage/' . $user->profile_photo_path,
                ];
            });

            return response()->json($users, 200);
        } catch (QueryException $e) {
            return response()->json(['error' => 'Failed to search users. Please try again later.'], 500);
        }
    }
    public function getUserStatsApi($userId)
    {
        try {
            // Fetch user details
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
            $totalBalls = $bowlingStats->sum('balls');
            $totalWickets = $bowlingStats->sum('wickets_taken');
            $totalOvers = $bowlingStats->sum('overs_bowled');
            $totalRunsConceded = $bowlingStats->sum('runs_conceded');
            $totalMatchesBowled = $bowlingStats->count();
            $bowlingEconomyRate = $totalOvers > 0 ? $totalRunsConceded / $totalOvers : 0;
            $totalMaidens = $bowlingStats->sum('maidens');
            $strikeRateBowling = $totalBalls / $totalWickets;
            $bestBowling = $bowlingStats->groupBy('match_id')
                ->reduce(function ($bestSoFar, $matchStats) {
                    $maxWickets = $matchStats->max('wickets_taken');
                    $minRuns = $matchStats->min('runs_conceded');
                    if (!$bestSoFar || $maxWickets > $bestSoFar['wickets'] || ($maxWickets === $bestSoFar['wickets'] && $minRuns < $bestSoFar['runs'])) {
                        return ['wickets' => $maxWickets, 'runs' => $minRuns];
                    }
                    return $bestSoFar;
                }, []);
            $bBowling = isset($bestBowling['wickets']) && $bestBowling['runs'] !== INF && $bestBowling['runs'] !== 0 ? (string)($bestBowling['wickets'] . '/' . $bestBowling['runs']) : 'N/A';
            // User Details
            $userDetails = $user;

            return response()->json([
                'user' => $userDetails,
                'batting' => [
                    'matches' => (string)$totalMatches,
                    'innings' => (string)$totalInnings,
                    'runs' => (string)$totalRuns,
                    'average' => (string)round($average, 2),
                    'highest' => (string)$highestScore,
                    'strikeRate' => (string)round($strikeRate, 2),
                    'fours' => (string)$totalFours,
                    'sixes' => (string)$totalSixes,
                    'fifties' => (string)$fifties,
                    'hundreds' => (string)$hundreds,
                ],
                'bowling' => [
                    'matches' => (string)$totalMatchesBowled,
                    'innings' => (string)$totalMatchesBowled, // Assuming 1 innings per match
                    'runs' => (string)$totalRunsConceded,
                    'overs' => (string)$totalOvers,
                    'strikeRate' => (string)round($strikeRateBowling, 2), // Calculate strike rate if needed
                    'maidens' => (string)$totalMaidens,
                    'wickets' => (string)$totalWickets,
                    'bBowling' => (string)$bBowling,
                    'ecoRate' => (string)round($bowlingEconomyRate, 2),
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'User stats not found'], 404);
        }
    }


    public function getUserStats($userId)
    {
        try {
            // Fetch user details
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
            $totalBalls = $bowlingStats->sum('balls');
            $totalWickets = $bowlingStats->sum('wickets_taken');
            $totalOvers = $bowlingStats->sum('overs_bowled');
            $totalRunsConceded = $bowlingStats->sum('runs_conceded');
            $totalMatchesBowled = $bowlingStats->count();
            $bowlingEconomyRate = $totalOvers > 0 ? $totalRunsConceded / $totalOvers : 0;
            $totalMaidens = $bowlingStats->sum('maidens');
            $strikeRateBowling = $totalBalls / $totalWickets;
            $bestBowling = $bowlingStats->groupBy('match_id')
                ->reduce(function ($bestSoFar, $matchStats) {
                    $maxWickets = $matchStats->max('wickets_taken');
                    $minRuns = $matchStats->min('runs_conceded');
                    if (!$bestSoFar || $maxWickets > $bestSoFar['wickets'] || ($maxWickets === $bestSoFar['wickets'] && $minRuns < $bestSoFar['runs'])) {
                        return ['wickets' => $maxWickets, 'runs' => $minRuns];
                    }
                    return $bestSoFar;
                }, []);

            // User Details
            $userDetails = [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'profile_photo_url' => $user->profile_photo_path,
            ];

            return response()->json([
                'user' => $userDetails,
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


    public function sendFriendRequest(Request $request)
    {
        $request->validate([
            'sender_id' => 'required|exists:users,id',
            'receiver_id' => 'required|exists:users,id',
        ]);

        $friendRequest = FriendRequest::create([
            'sender_id' => $request->sender_id,
            'receiver_id' => $request->receiver_id,
            'status' => 'pending',
        ]);

        return response()->json(['message' => 'Friend request sent', 'request' => $friendRequest]);
    }

    public function confirmFriendRequest(Request $request, $requestId)
    {
        try {
            $friendRequest = FriendRequest::findOrFail($requestId);

            $friendRequest->update(['status' => 'accepted']);

            Friendship::create([
                'user1_id' => $friendRequest->sender_id,
                'user2_id' => $friendRequest->receiver_id,
                'status' => 'active',
            ]);

            return response()->json(['message' => 'Friend request confirmed']);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Friend request not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Something went wrong'], 500);
        }
    }

    public function rejectFriendRequest(Request $request, $requestId)
    {
        try {
            $friendRequest = FriendRequest::findOrFail($requestId);

            $friendRequest->update(['status' => 'declined']);

            return response()->json(['message' => 'Friend request rejected']);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Friend request not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Something went wrong'], 500);
        }
    }

    public function searchFriendRequests(Request $request, $userId)
    {
        try {
            $friendRequests = FriendRequest::where('receiver_id', $userId)->get();

            return response()->json(['friend_requests' => $friendRequests]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Something went wrong'], 500);
        }
    }

    public function pendingFriendRequests(Request $request)
    {
        try {
            $userId = $request->input('user_id');

            $friendRequests = FriendRequest::where('receiver_id', $userId)
                ->where('status', 'pending')
                ->join('users', 'friend_requests.sender_id', '=', 'users.id')
                ->select('friend_requests.*', 'users.username as sender_username', 'users.name as sender_name', 'users.profile_photo_path')
                ->get();

            return response()->json(['friend_requests' => $friendRequests], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Something went wrong'], 500);
        }
    }

    public function searchFriendList(Request $request, $userId)
    {
        try {
            $friendships = Friendship::where(function ($query) use ($userId) {
                $query->where('user1_id', $userId)
                    ->orWhere('user2_id', $userId);
            })->where('status', 'active')->get();

            $friendIds = $friendships->pluck('user1_id')->merge($friendships->pluck('user2_id'));

            $friends = User::whereIn('id', $friendIds)->get();

            return response()->json(['friends' => $friends]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Something went wrong'], 500);
        }
    }
}
