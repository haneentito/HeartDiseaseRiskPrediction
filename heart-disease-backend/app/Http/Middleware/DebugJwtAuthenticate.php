<?php
namespace App\Http\Middleware;

use Closure;
use Tymon\JWTAuth\Http\Middleware\Authenticate;
use Illuminate\Support\Facades\Log;

class DebugJwtAuthenticate extends Authenticate
{
    public function handle($request, Closure $next, ...$guards)
    {
        Log::info('DebugJwtAuthenticate - Request URL: ' . $request->url());
        Log::info('DebugJwtAuthenticate - Guards: ' . json_encode($guards));
        Log::info('DebugJwtAuthenticate - Token: ' . $request->header('Authorization'));
        // app/Http/Middleware/DebugJwtAuthenticate.php
try {
    return parent::handle($request, $next, ...$guards);
} catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
    Log::error('Token expired: ' . $e->getMessage());
    return response()->json(['message' => 'Token has expired'], 401);
} catch (\Exception $e) {
    Log::error('DebugJwtAuthenticate - Error: ' . $e->getMessage());
    throw $e;
}
    }
}