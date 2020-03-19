<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        \Illuminate\Auth\AuthenticationException::class,
        \Illuminate\Auth\Access\AuthorizationException::class,
        \Symfony\Component\HttpKernel\Exception\HttpException::class,
        \Illuminate\Database\Eloquent\ModelNotFoundException::class,
        \Illuminate\Session\TokenMismatchException::class,
        \Illuminate\Validation\ValidationException::class,
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
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        if ($this->shouldReport($exception)) {
            //$airbrakeNotifier = \App::make('Airbrake\Notifier');
            //$airbrakeNotifier->notify($exception);
        }
        //below line is causing issue in laravel default exception handler if public constructor method is defined here so removed that method
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
        if ($request->hasCookie('locale')) {
            $cookie = $request->cookie('locale');
            $locale = strlen($cookie) > 2 ? decrypt($cookie, false) : $cookie;
            if (in_array($locale, \Config::get('app.locales'))) {
                app()->setLocale($locale);
            } else {
                app()->setLocale($locale);
            }
        }

        if (env('APP_ENV') == 'local') {
            // return parent::render($request, $exception);
        }

        if ($exception instanceof \Illuminate\Database\QueryException) {
            return response()->view('errors.genericException', ['code' => 400, 'description' => trans('messages.exceptions.query_exception')], 400);
        }
        if ($exception instanceof \jdavidbakr\MailTracker\Exceptionmails\BadUrlLink) {
            return response()->view('errors.genericException', ['code' => 400, 'description' => trans('messages.exceptions.query_exception')], 400);
        }

        if ($this->isHttpException($exception)) {
            switch ($exception->getStatusCode()) {
                case 404:
                    return response()->view('errors.genericException', ['code' => 404, 'description' => trans('messages.exceptions.page_no_found')], 404);
                    break;
                case 403:
                    return response()->view('errors.genericException', ['code' => 403, 'description' => trans('messages.exceptions.forbidden')], 403);
                    break;
                case 419:
                    return response()->view('errors.genericException', ['code' => 419, 'description' => trans('messages.exceptions.page_expired')], 419);
                    break;
                case 429:
                    return response()->view('errors.genericException', ['code' => 429, 'description' => trans('messages.exceptions.too_many_request')], 429);
                    break;
                case 500:
                    return response()->view('errors.genericException', ['code' => 500, 'description' => trans('messages.exceptions.error_500')], 500);
                    break;

                case 503:
                    return response()->view('errors.genericException', ['code' => 503, 'description' => trans('messages.exceptions.service_unavailable')], 503);
                    break;

                default:
                    return response()->view('errors.genericException', ['code' => $exception->getStatusCode(), 'description' => trans('messages.exceptions.no_msg_available')], $exception->getStatusCode());
                    break;
            }

        }

        if ($exception instanceof AuthenticationException) {
            return parent::render($request, $exception);
        }

        if ($exception instanceof TokenMismatchException) {
            return redirect(route('dashboard'))->with('alert-info', 'Session expired. Please try again');
        }
        if ($exception instanceof InvalidArgumentException) {
            if (env('APP_ENV') == 'local') {
                dd(get_defined_vars());
            } else {
                return response()->view('errors.genericException', ['code' => 500, 'description' => trans('messages.exceptions.error_500')], 500);
            }
        }

        if ($exception instanceof Illuminate\Validation\ValidationException) {
            return parent::render($request, $exception);
        }

        if ($exception) {
            if (env('APP_ENV') != 'local') {
                $error_code = 500;
                if ($request->ajax()) {
                    return response()->json([
                        'status' => $error_code,
                        'error'  => trans('messages.exceptions.no_msg_available'),
                    ]);
                } else {
                    return response()->view('errors.genericException',
                        [
                            'code'        => $error_code,
                            'description' => trans('messages.exceptions.no_msg_available'),
                        ],
                        $error_code);
                }
            }
        }

        return parent::render($request, $exception);
    }

    /**
     * Convert an authentication exception into an unauthenticated response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Auth\AuthenticationException  $exception
     * @return \Illuminate\Http\Response
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->expectsJson()) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        return redirect()->guest(route('login'));
    }
}
