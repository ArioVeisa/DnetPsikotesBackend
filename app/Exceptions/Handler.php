<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
// Import exception dari package JWT
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        // Tangkap error jika token tidak valid (misal: token diubah manual)
        $this->renderable(function (TokenInvalidException $e, $request) {
            return response()->json(['error' => 'Token is invalid'], 401);
        });

        // Tangkap error jika token sudah expired
        $this->renderable(function (TokenExpiredException $e, $request) {
            return response()->json(['error' => 'Token has expired'], 401);
        });

        // Tangkap error jika token tidak ditemukan di request
        $this->renderable(function (JWTException $e, $request) {
            return response()->json(['error' => 'Token not provided'], 401);
        });

        // Tangkap error 404 (Endpoint tidak ditemukan)
        $this->renderable(function (NotFoundHttpException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json(['error' => 'Resource not found.'], 404);
            }
        });

        // Tangkap semua error lainnya (Internal Server Error 500)
        $this->renderable(function (Throwable $e, $request) {
            if ($request->is('api/*')) {
                // Di lingkungan produksi, jangan tampilkan detail error
                if (!app()->environment('local')) {
                    return response()->json(['error' => 'Internal Server Error.'], 500);
                }
                // Di lokal, tampilkan detail error untuk debugging
                return response()->json([
                    'error' => 'Internal Server Error.',
                    'message' => $e->getMessage()
                ], 500);
            }
        });
    }
}