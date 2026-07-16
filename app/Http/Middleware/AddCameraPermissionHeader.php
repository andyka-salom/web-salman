<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AddCameraPermissionHeader
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Tambahkan Permissions-Policy header
        $response->headers->set('Permissions-Policy', 'camera=(self), microphone=()');

        return $response;
    }
}
