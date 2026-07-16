<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\EnsureUserIsActive;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\AddCameraPermissionHeader;
use App\Http\Middleware\SessionTimeout;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Register middleware aliases
        $middleware->alias([
            'active' => EnsureUserIsActive::class,
            'security.headers' => SecurityHeaders::class,
            'camera.permission' => AddCameraPermissionHeader::class,
            'session.timeout' => SessionTimeout::class,
        ]);

        // Apply to web middleware group
        $middleware->web(append: [
            SecurityHeaders::class,
            SessionTimeout::class,
            EnsureUserIsActive::class,
            AddCameraPermissionHeader::class,
        ]);

        // Apply to API middleware group
        $middleware->api(append: [
            SecurityHeaders::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // CRITICAL: Disable default error views untuk TokenMismatchException
        $exceptions->dontReport([
            TokenMismatchException::class,
        ]);

        // 1. Handle TokenMismatchException (419 CSRF Token Mismatch) - AUTO LOGOUT
        // PENTING: Handler ini HARUS yang pertama agar tidak tertimpa view default
        $exceptions->render(function (TokenMismatchException $e, Request $request) {
            // Logout user dan invalidate session
            if (Auth::check()) {
                Auth::logout();
            }

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            $statusCode = 419;

            // Jika request API atau mengharapkan JSON
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'message' => 'Session expired. Please login again.',
                    'status' => $statusCode,
                    'redirect' => route('login'),
                ], $statusCode);
            }

            // Untuk web request, PAKSA redirect ke login (AUTO LOGOUT)
            // Menggunakan redirect() dengan flash message
            return redirect()->route('login')
                ->with('error', 'Sesi Anda telah berakhir. Silakan login kembali.')
                ->with('info', 'Untuk keamanan, Anda telah otomatis logout.');
        });

        // 1b. Handle HttpException 419 sebagai fallback
        $exceptions->render(function (HttpException $e, Request $request) {
            if ($e->getStatusCode() === 419) {
                // Logout user
                if (Auth::check()) {
                    Auth::logout();
                }

                $request->session()->invalidate();
                $request->session()->regenerateToken();

                // Redirect ke login
                if ($request->is('api/*') || $request->expectsJson()) {
                    return response()->json([
                        'message' => 'Session expired. Please login again.',
                        'status' => 419,
                        'redirect' => route('login'),
                    ], 419);
                }

                return redirect()->route('login')
                    ->with('error', 'Sesi Anda telah berakhir. Silakan login kembali.')
                    ->with('info', 'Untuk keamanan, Anda telah otomatis logout.');
            }

            // Handle other HTTP exceptions
            $statusCode = $e->getStatusCode();
            $message = $e->getMessage() ?: \Illuminate\Http\Response::$statusTexts[$statusCode] ?? 'An error occurred';

            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'message' => $message,
                    'status' => $statusCode
                ], $statusCode);
            }

            return response()->view('errors.http-error', [
                'exception' => $e,
                'statusCode' => $statusCode,
                'message' => $message,
            ], $statusCode);
        });

        // 2. Handle AuthenticationException (401 Unauthorized)
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            $statusCode = 401;

            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'message' => 'Unauthenticated. Please login first.',
                    'status' => $statusCode,
                ], $statusCode);
            }

            return redirect()->guest(route('login'));
        });

        // 3. Handle ValidationException (422 Unprocessable Entity)
        $exceptions->render(function (ValidationException $e, Request $request) {
            $statusCode = 422;

            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'status' => $statusCode,
                    'errors' => $e->errors(),
                ], $statusCode);
            }

            return back()
                ->withInput($request->except('password', 'password_confirmation'))
                ->withErrors($e->errors());
        });

        // 4. Handle 404 specifically (Resource Not Found)
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            $statusCode = 404;
            $message = 'Resource not found';

            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'message' => $message,
                    'status' => $statusCode,
                ], $statusCode);
            }

            return response()->view('errors.http-error', [
                'exception' => $e,
                'statusCode' => $statusCode,
                'message' => $message,
            ], $statusCode);
        });
    })
    ->create();
