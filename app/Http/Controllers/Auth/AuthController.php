<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $name = trim((string) $request->input('name', ''));
        $email = trim((string) $request->input('email', ''));
        $password = (string) $request->input('password', '');
        $neighborhoodId = $request->filled('neighborhoodId') ? (int) $request->input('neighborhoodId') : null;

        if ($name === '' || $email === '' || strlen($password) < 8) {
            return $this->jsonError('Name, email, and password (min 8 chars) are required.', 422);
        }

        if (User::where('email', $email)->exists()) {
            return $this->jsonError('An account with this email already exists.', 409);
        }

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password_hash' => Hash::make($password),
            'role' => 'resident',
            'status' => 'active',
            'neighborhood_id' => $neighborhoodId,
        ]);

        if ($neighborhoodId) {
            $user->subscriptions()->syncWithoutDetaching([$neighborhoodId]);
        }

        Notification::forceCreate([
            'user_id' => $user->id,
            'type' => 'info',
            'title' => 'Welcome to AquaWatch Nairobi',
            'message' => 'Start by reporting water status in your neighbourhood or subscribing to alerts.',
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return $this->jsonSuccess(['user' => new UserResource($user->load('neighborhood'))], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $email = trim((string) $request->input('email', ''));
        $password = (string) $request->input('password', '');

        $user = User::with('neighborhood')->where('email', $email)->first();

        if (! $user || ! Hash::check($password, $user->password_hash)) {
            return $this->jsonError('Invalid email or password.', 401);
        }

        if ($user->status === 'suspended') {
            return $this->jsonError('Your account has been suspended. Contact an administrator.', 409);
        }

        Auth::login($user);
        $request->session()->regenerate();

        return $this->jsonSuccess(['user' => new UserResource($user)]);
    }

    public function logout(Request $request): JsonResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return $this->jsonSuccess();
    }

    public function me(): JsonResponse
    {
        $user = Auth::user();

        if (! $user) {
            return $this->jsonSuccess(['user' => null]);
        }

        return $this->jsonSuccess(['user' => new UserResource($user->load('neighborhood'))]);
    }
}
