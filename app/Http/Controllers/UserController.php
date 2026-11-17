<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Requests\CreateUserRequest;
use App\Models\User;
use App\Services\Auth\UserCreator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

final class UserController
{
    public function create(Request $request): View
    {
        Gate::forUser($request->user())->authorize('create', [User::class, UserRole::Editor]);
        $roles = $request->user()->role === UserRole::SuperAdministrator
            ? UserRole::cases()
            : [UserRole::Editor];

        return view('users.create', ['roles' => $roles]);
    }

    public function store(CreateUserRequest $request, UserCreator $creator): RedirectResponse
    {
        $validated = $request->validated();
        $role = UserRole::from($validated['role']);
        unset($validated['role']);
        $user = $creator->create($request->user(), $role, $validated);

        return redirect()->route('users.create')->with('status', "La cuenta de {$user->name} fue creada.");
    }
}
