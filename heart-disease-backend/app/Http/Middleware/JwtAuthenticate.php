<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Illuminate\Support\Facades\Log;

class JwtAuthenticate
{
    public function handle(Request $request, Closure $next)
    {
        Log::info('JwtAuthenticate middleware triggered for request: ' . $request->path() . ', Token: ' . $request->header('Authorization'));

       try {
    $user = JWTAuth::guard('doctor')->parseToken()->authenticate();
    Log::info('Token authenticated successfully for user: ' . $user->id);
} catch (TokenExpiredException $e) {
    Log::error('Token expired: ' . $e->getMessage());
    return response()->json(['error' => 'Token expired'], 401)->header('Content-Type', 'application/json');
} catch (TokenInvalidException $e) {
            Log::error('Token invalid: ' . $e->getMessage());
            return response()->json(['error' => 'Token invalid'], 401)->header('Content-Type', 'application/json');
        } catch (JWTException $e) {
            Log::error('Token absent or error: ' . $e->getMessage());
            return response()->json(['error' => 'Token absent'], 401)->header('Content-Type', 'application/json');
        }

        return $next($request)->header('Content-Type', 'application/json');
    }
}