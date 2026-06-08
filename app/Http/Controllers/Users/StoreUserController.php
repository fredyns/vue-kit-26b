<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\Users\StoreUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class StoreUserController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(StoreUserRequest $request): RedirectResponse
    {
        $user = DB::transaction(function () use ($request) {
            $user = User::create([
                'name' => $request->validated('name'),
                'email' => $request->validated('email'),
                'password' => $request->validated('password'),
            ]);

            if ($request->has('roles')) {
                $user->addRoleNames($request->validated('roles', []));
            }

            return $user;
        });

        return redirect()->route('users.show', $user)->with('success', 'User created successfully.');
    }
}
