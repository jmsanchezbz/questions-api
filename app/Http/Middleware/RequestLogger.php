<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Log;

class RequestLogger
{
    public function handle($request, Closure $next)
    {
        $method = $request->getMethod();
        $url = $request->fullUrl();
        $ip = $request->ip();
        $headers = $request->headers->all();

        // Log to a file:
        // Log::channel('request-log')->info("{$method} {$url} - {$ip} - Headers: " . json_encode($headers));

        // Log to the console:
        Log::info("{$method} {$url} - {$ip} - Headers: " . json_encode($headers));
        Log::channel('request-log')->info("{$method} {$url} - {$ip} - Headers: " . json_encode($headers));

        return $next($request);
    }
}
