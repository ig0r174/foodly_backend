<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Jenssegers\Agent\Agent;

class UserController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @return JsonResponse
     */
    public function __invoke(Request $request): JsonResponse
    {
        $platform = (stristr($request->userAgent(), "Android") ? "Android" : preg_match("/iPhone|iPad|iPod/", $request->userAgent())) ? "iOS" : "Web";

        $user = User::create([
            'api_token' => Str::random(60),
            'ip' => $request->ip(),
            'platform' => $platform
        ]);

        return response()->json([
            'token' => $user['api_token']
        ]);
    }
}
