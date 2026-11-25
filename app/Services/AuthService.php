<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    /**
     * Authenticate admin user and issue API Bearer token.
     */
    public function login(string $email, string $password): array
    {
        $user = User::where('email', $email)->first();

        if (! $user || ! Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials do not match our records.'],
            ]);
        }

        if (! $user->is_admin) {
            throw ValidationException::withMessages([
                'email' => ['Access denied. Administrator privileges required.'],
            ]);
        }

        // Revoke previous tokens or generate a new token
        $token = $user->createToken('admin-api-token')->plainTextToken;

        return [
            'token' => $token,
            'user' => $user,
        ];
    }

    /**
     * Invalidate current user token.
     */
    public function logout(User $user): void
    {
        $user->currentAccessToken()?->delete();
    }
}
