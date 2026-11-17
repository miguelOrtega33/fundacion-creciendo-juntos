<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Requests\CreateUserRequest;
use App\Models\User;
use App\Queries\VisibleUsersQuery;
use App\Services\Auth\UserCreator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

final class UserController
{
    public function index(Request $request, VisibleUsersQuery $visibleUsers): View
    {
        Gate::forUser($request->user())->authorize('viewAny', User::class);

        return view('users.index', [
            'users' => $visibleUsers->for($request->user())->paginate(10),
        ]);
    }

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
