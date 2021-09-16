<?php

namespace App\Exceptions;

use Sentry;
use Throwable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        TaskAddException::class,
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param \Throwable $exception
     *
     * @throws \Exception
     *
     * @return void
     */
    public function report(Throwable $exception)
    {
        parent::report($exception);

        if (app()->bound('sentry') && $this->shouldReport($exception)) {
            $this->setSentryVersion();

            Sentry::captureException($exception);
        }

        parent::report($exception);
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
            /* @var TaskAddException $exception */
            return $exception->toResponse();
        }

        $response = parent::render($request, $exception);

        if (
            ! app()->isProduction() &&
            $response->getStatusCode() >= Response::HTTP_INTERNAL_SERVER_ERROR
        ) {
            dd($exception);
        }

        return $response;
    }

    protected function setSentryVersion()
    {
        try {
            Config::set('sentry.release', Cache::get('app.version', ''));
        } catch (\Exception $e) {
        }
    }
}
