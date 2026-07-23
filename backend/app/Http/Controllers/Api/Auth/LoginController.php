<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        /** @var User|null $user */
        $user = User::query()
            ->where('email', strtolower($credentials['email']))
            ->first();

        if (
            ! $user ||
            ! Hash::check($credentials['password'], $user->password)
        ) {
            throw ValidationException::withMessages([
                'email' => ['A megadott email-cím vagy jelszó helytelen.'],
            ]);
        }

        if (! $user->active) {
            throw ValidationException::withMessages([
                'email' => ['Ez a felhasználói fiók inaktív.'],
            ]);
        }

        $token = $user
            ->createToken('api-token')
            ->plainTextToken;

        return response()->json([
            'message' => 'Sikeres bejelentkezés.',
            'token' => $token,
            'user' => $user,
        ]);
    }
}