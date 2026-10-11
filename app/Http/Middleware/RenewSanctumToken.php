<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

class RenewSanctumToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, int $minutes = 10): Response
    {
        $response = $next($request);

        $token = $request->user()?->currentAccessToken();

        // TransientToken (SPA/session auth) cant't be saved, so check the type
        if ($token instanceof PersonalAccessToken) {
            if (! $token-> expires_at || $token->expires_at->lt(now()->addMinutes($minutes/2))) {
                $token->forceFill(['expires_at' => now()->addMinutes($minutes)])->save();
            }
        }

        return $response;
    }
}
