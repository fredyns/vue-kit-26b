<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\Users\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class UpdateUserController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(UpdateUserRequest $request, User $user): RedirectResponse
    {
        DB::transaction(function () use ($request, $user) {
            $data = [
                'name' => $request->validated('name'),
                'email' => $request->validated('email'),
            ];

            if ($data['email'] !== $user->email) {
                $data['email_verified_at'] = null;
            }

            $user->update($data);

            if ($request->has('roles')) {
                $newRoles = $request->validated('roles', []);
                $currentRoles = $user->webRoles->pluck('name')->toArray();
                $toRemove = array_diff($currentRoles, $newRoles);
                $toAdd = array_diff($newRoles, $currentRoles);

                if (count($toRemove) > 0) {
                    $user->removeRoleNames($toRemove);
                }

                if (count($toAdd) > 0) {
                    $user->addRoleNames($toAdd);
                }
            }
        });

        return redirect()->route('users.show', $user)->with('success', 'User updated successfully.');
    }
}
