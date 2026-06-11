<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\Users\UpdatePasswordUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

class UpdatePasswordUserController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(UpdatePasswordUserRequest $request, User $user): RedirectResponse
    {
        $user->update([
            'password' => $request->validated('password'),
        ]);

        return redirect()->route('users.show', $user)->with('success', 'Password changed successfully.');
    }
}
