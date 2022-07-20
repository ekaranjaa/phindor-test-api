<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function index(Request $request): UserResource
    {
        $user = $request->user();
        return new UserResource($user);
    }

    /**
     * @throws ValidationException
     */
    public function login(Request $request): string
    {
        $request->validate([
            'password' => 'required',
            'email' => 'email|required'
        ]);

        $user = User::query()->where('email', $request->input('email'))->first();
        if (!$user) throw ValidationException::withMessages([
            'email' => ['No such user.']
        ]);

        if (!Hash::check($request->input('password'), $user->password)) {
            throw ValidationException::withMessages([
                'password' => ['Incorrect password.'],
            ]);
        }

        $user->tokens()->delete();
        return $user->createToken($user['email'])->plainTextToken;
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        $credentials = $request->validated();

        $user = User::query()->create([
            ...$credentials,
            'password' => Hash::make($credentials['password'])
        ]);

        event(new Registered($user));
        return response()->json(['message' => 'Account created.']);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out.']);
    }
}
