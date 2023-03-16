<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EnsureTokenIsValid
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return JsonResponse
     */
    public function handle(Request $request, Closure $next): JsonResponse
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['message' => 'No token provided'], 401);
        }

        if (!$user = User::where('api_token', $token)->first()) {
            return response()->json(['message' => 'Token is invalid'], 401);
        }

        $request->attributes->add(['user_id' => $user->id]);

        return $next($request);
    }
}
