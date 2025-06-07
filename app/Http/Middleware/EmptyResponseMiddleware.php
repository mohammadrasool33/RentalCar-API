<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EmptyResponseMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        
        // Check if the response is JSON and has content
        if ($response->headers->get('Content-Type') === 'application/json') {
            $content = $response->getContent();
            
            // If the content is "Resource was not found" JSON, check if it should be an empty collection instead
            if ($content && strpos($content, 'Resource was not found') !== false) {
                // For endpoints that should return collections
                if (strpos($request->path(), 'rentals/car/') !== false) {
                    return response()->json([], 200);
                }
            }
        }
        
        return $response;
    }
}
