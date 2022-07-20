<?php

namespace App\Http\Controllers;

use Illuminate\Auth\Events\Verified;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class EmailVerificationController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(['message' => 'Verify your email']);
    }

    public function verify(Request $request): JsonResponse
    {
        if (
            (int)$request->route('id') === (int)$request->user()->getKey() &&
            $request->user()->markEmailAsVerified()
        ) {
            event(new Verified($request->user()));
        }

        return response()->json(['message' => 'Email verification complete']);
    }

    /**
     * @throws ValidationException
     */
    public function resend(Request $request): JsonResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            throw ValidationException::withMessages(['email' => 'User already has a verified email!']);
        }

        $request->user()->sendEmailVerificationNotification();
        return response()->json(['message' => 'Email verification notification has been resent!']);
    }
}
