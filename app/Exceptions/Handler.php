<?php

namespace App\Exceptions;

use Sentry;
use Throwable;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
    protected $dontReport = [
        TaskAddException::class,
    ];

    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    public function report(Throwable $exception)
    {
        parent::report($exception);

        if (App::bound('sentry') && $this->shouldReport($exception)) {
            $this->setSentryVersion();

            Sentry::captureException($exception);
        }
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Throwable               $exception
     *
     * @throws \Throwable
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function render($request, Throwable $exception)
    {
        if ($exception instanceof TaskAddException) {
            return $exception->toResponse();
        }

        return parent::render($request, $exception);
    }

    protected function setSentryVersion()
    {
        try {
            Config::set('sentry.release', Cache::get('app.version', ''));
        } catch (\Exception $e) {
        }
    }
}
