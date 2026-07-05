<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $users = User::with('neighborhood')
            ->roleFilter($request->string('role')->toString() ?: null)
            ->statusFilter($request->string('status')->toString() ?: null)
            ->searchTerm($request->string('search')->toString() ?: null)
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->jsonSuccess([
            'users' => UserResource::collection($users),
            'stats' => $this->countByStatus(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $name = trim((string) $request->input('name', ''));
        $email = trim((string) $request->input('email', ''));
        $role = $request->input('role', 'resident');
        $neighborhoodId = $request->filled('neighborhoodId') ? (int) $request->input('neighborhoodId') : null;

        if ($name === '' || $email === '') {
            return $this->jsonError('Name and email are required.', 422);
        }

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password_hash' => Hash::make('changeme123'),
            'role' => in_array($role, ['resident', 'admin'], true) ? $role : 'resident',
            'status' => 'active',
            'neighborhood_id' => $neighborhoodId,
        ]);

        return $this->jsonSuccess(['user' => new UserResource($user->load('neighborhood'))], 201);
    }

    public function suspend(int $id): JsonResponse
    {
        return $this->setStatus($id, 'suspended');
    }

    public function activate(int $id): JsonResponse
    {
        return $this->setStatus($id, 'active');
    }

    private function setStatus(int $id, string $status): JsonResponse
    {
        $user = User::findOrFail($id);
        $user->update(['status' => $status]);

        return $this->jsonSuccess(['user' => new UserResource($user->load('neighborhood'))]);
    }

    private function countByStatus(): array
    {
        return [
            'total' => User::count(),
            'active' => User::where('status', 'active')->count(),
            'suspended' => User::where('status', 'suspended')->count(),
            'admins' => User::where('role', 'admin')->count(),
        ];
    }
}
