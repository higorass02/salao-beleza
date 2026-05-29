<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    public function register()
    {
        $this->renderable(function (AppointmentConflictException $exception, $request) {
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'message' => $exception->getMessage(),
                ], 422);
            }

            return back()->withErrors(['starts_at' => $exception->getMessage()]);
        });
    }
}
