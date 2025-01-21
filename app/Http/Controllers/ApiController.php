<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\UserSession;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ApiController extends Controller {

    public function ping(Request $request){
        return $request;
    }

    public function register(Request $request) {
        try {
            $validated = $request->validate([
                'name' => 'required|string',
                'email' => 'required|email|unique:users',
                'password' => 'required|min:8',
            ]);

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => bcrypt($validated['password']),
            ]);

            return response()->json(['token' => $user->createToken('API Token')->plainTextToken]);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }
    }

    public function login(Request $request) {
        try {
            $validated = $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);

            $user = User::where('email', $validated['email'])->first();

            if (!$user || !Hash::check($validated['password'], $user->password)) {
                return response()->json(['error' => 'Invalid credentials'], 401);
            }

            return response()->json([
                'token' => $user->createToken('API Token')->plainTextToken,
                'message' => 'Login successful',
            ]);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }
    }

    public function storeClient(Request $request) {
        try {
            $validated = $request->validate([
                'name' => 'required|string',
                'email' => 'required|email|unique:clients',
            ]);

            $client = Auth::user()->clients()->create($validated);
            return response()->json($client, 201);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }
    }

    public function updateClient(Request $request, Client $client) {
        try {
            if ($client->coach_id !== Auth::id()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
    
            $validated = $request->validate([
                'name' => 'sometimes|required|string',
                'email' => 'sometimes|required|email|unique:clients,email,' . $client->id,
            ]);
    
            $client->update($validated);
            return response()->json($client);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred'], 500);
        }
    }

    public function deleteClient(Client $client) {
        try {
            if ($client->coach_id !== Auth::id()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
            $client->delete();
            return response()->noContent();
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while deleting the client'], 500);
        }
    }

    public function uncompletedSessions() {
        $sessions = UserSession::whereHas('client', function ($query) {
            $query->where('coach_id', Auth::id());
        })->where('completed', false)->get();

        return response()->json($sessions);
    }

    public function completeSession(UserSession $session) {
        if ($session->client->coach_id != Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $session->update(['completed' => true]);
        return response()->json(['message' => 'Session marked as completed.']);
    }

    public function analytics() {
        $user = Auth::user();
        $totalSessions = $user->userSessions()->count();
        $completedSessions = $user->userSessions()->where('completed', true)->count();
        $progress = $totalSessions > 0 ? ($completedSessions / $totalSessions) * 100 : 0;

        return response()->json([
            'total_sessions' => $totalSessions,
            'completed_sessions' => $completedSessions,
            'progress_percentage' => $progress,
        ]);
    }
}
